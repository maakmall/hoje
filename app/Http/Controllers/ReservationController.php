<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VariantBeverage;
use App\Helpers\Numeric;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Location;
use App\Models\Menu;
use App\Models\MenuPrice;
use App\Models\Reservation;
use App\Rules\ReservationCapacity;
use App\Services\Midtrans;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(): View
    {
        return view('reservation', [
            'title' => 'Reservation',
            'locations' => Location::all(),
            'menus' => Menu::with('prices')->available()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'date' => ['required', 'string'],
            'number_of_people' => ['required', 'integer', 'min:1'],
            'location_id' => 'required|exists:locations,id',
            'notes' => 'nullable|string',
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'table' => 'nullable|integer|exists:tables,id',
            'cart' => 'required|array|min:1',
            'cart.*.menu_id' => 'required|exists:menus,id',
            'cart.*.qty' => 'required|integer|min:1',
            'cart.*.variant' => ['nullable', Rule::in(VariantBeverage::values())],
        ]);
        return DB::transaction(function () use ($validated) {
            try {
                $datetime = Carbon::createFromFormat('m/d/Y h:i A', $validated['date']);
            } catch (\Exception $e) {
                abort(422, 'Format tanggal tidak valid. Gunakan format: mm/dd/yyyy hh:mm AM/PM');
            }
            
            $totalBooked = Reservation::whereDate('datetime', $datetime)
                ->where('location_id', $validated['location_id'])
                ->sum('number_of_people');

            $location = Location::find($validated['location_id']);

            $availableCapacity = $location->capacity - $totalBooked;

            if ($validated['number_of_people'] > $availableCapacity) {
                return response()->json([
                    'message' => "Kapasitas penuh. Tersisa $availableCapacity kursi."
                ], 422);
            }

            $reservation = Reservation::create([
                'id' => Numeric::generateId('reservations'),
                'customer_name' => $validated['customer_name'],
                'datetime' => $datetime,
                'location_id' => $validated['location_id'],
                'number_of_people' => $validated['number_of_people'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $order = $reservation->order()->create([
                'id' => Numeric::generateId('orders'),
                'datetime' => $datetime,
                'table_id' => $validated['table'] ?? null,
            ]);

            $orderMenusData = collect($validated['cart'])->map(function ($item) {
                $price = MenuPrice::where('menu_id', $item['menu_id'])
                    ->where('variant_beverage', $item['variant'] ?? null)
                    ->value('price');

                if (is_null($price)) {
                    abort(422, 'Harga menu tidak ditemukan.');
                }

                return [
                    'menu_id' => $item['menu_id'],
                    'variant_beverage' => $item['variant'] ?? null,
                    'quantity' => $item['qty'],
                    'subtotal_price' => $price * $item['qty'],
                ];
            });

            $order->orderMenus()->createMany($orderMenusData->toArray());

            $amount = $orderMenusData->sum('subtotal_price');
            $amount -= $amount * 0.5; // potong DP 50%

            $paymentMethod = PaymentMethod::from($validated['payment_method']);
            $paymentData = [
                'id' => Numeric::generateId('payments'),
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $paymentMethod,
                'status' => PaymentStatus::Pending,
                'datetime' => $datetime,
            ];

            if ($paymentMethod === PaymentMethod::Cash) {
                $paymentData['status'] = PaymentStatus::Paid;
            } else {
                $midtrans = new Midtrans();

                $payload = [
                    'payment_type' => $paymentMethod === PaymentMethod::Qris ? 'gopay' : 'bank_transfer',
                    'transaction_details' => [
                        'order_id' => $order->id . 'DP',
                        'gross_amount' => $amount,
                    ],
                ];

                if ($paymentMethod === PaymentMethod::Transfer) {
                    $payload['bank_transfer'] = ['bank' => 'bca'];
                }

                $response = $midtrans->createTransaction($payload);

                $paymentData['transaction_id'] = $response->transaction_id ?? null;
                $paymentData['va_number'] = $response->va_numbers[0]->va_number ?? null;
                $paymentData['qr_url'] = $response->actions[0]->url ?? null;
            }

            $payment = $order->payments()->create($paymentData);

            return response()->json([
                'order_id' => $order->id,
                'payment' => [
                    'va_number' => $payment->va_number,
                    'qr_url' => $payment->qr_url,
                ],
            ]);
        });
    }
}
