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
use App\Services\Midtrans;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'customer_phone' => 'required|string|max:15',
            'customer_email' => 'required|string|email|max:50',
            'date' => ['required', 'string'],
            'number_of_people' => ['required', 'integer', 'min:1'],
            'location_id' => 'required|exists:lokasi,id',
            'note' => 'nullable|string',
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'table' => 'nullable|integer|exists:meja,id',
            'cart' => 'required|array|min:1',
            'cart.*.menu_id' => 'required|exists:menu,id',
            'cart.*.qty' => 'required|integer|min:1',
            'cart.*.variant' => ['nullable', Rule::in(VariantBeverage::values())],
        ]);
        return DB::transaction(function () use ($validated) {
            try {
                $datetime = Carbon::createFromFormat('m/d/Y h:i A', $validated['date']);
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'date' => 'Invalid date format. Please use "MM/DD/YYYY HH:MM AM/PM".',
                ]);
            }
            
            $totalBooked = Reservation::whereDate('waktu', $datetime)
                ->where('id_lokasi', $validated['location_id'])
                ->sum('jumlah_orang');

            $location = Location::find($validated['location_id']);

            $availableCapacity = $location->kapasitas - $totalBooked;

            if ($validated['number_of_people'] > $availableCapacity) {
                throw ValidationException::withMessages([
                    'number_of_people' => "Full capacity reached for this location on the selected date. Available capacity: $availableCapacity",
                ]);
            }

            $reservation = Reservation::create([
                'id' => Numeric::generateId('reservations'),
                'nama_pelanggan' => $validated['customer_name'],
                'telepon_pelanggan' => $validated['customer_phone'],
                'email_pelanggan' => $validated['customer_email'],
                'waktu' => $datetime,
                'id_lokasi' => $validated['location_id'],
                'jumlah_orang' => $validated['number_of_people'],
                'catatan' => $validated['note'] ?? null,
            ]);

            $order = $reservation->order()->create([
                'id' => Numeric::generateId('orders'),
                'waktu' => $datetime,
                // 'table_id' => $validated['table'] ?? null,
            ]);

            $orderMenusData = collect($validated['cart'])->map(function ($item) {
                $price = MenuPrice::where('id_menu', $item['menu_id'])
                    ->where('variasi_minuman', $item['variant'] ?? null)
                    ->value('harga');

                if (is_null($price)) {
                    throw ValidationException::withMessages([
                        'cart' => ['Invalid menu or variant selected.'],
                    ]);
                }

                return [
                    'id_menu' => $item['menu_id'],
                    'variasi_minuman' => $item['variant'] ?? null,
                    'jumlah' => $item['qty'],
                    'subtotal_harga' => $price * $item['qty'],
                ];
            });

            $order->orderMenus()->createMany($orderMenusData->toArray());

            $amount = $orderMenusData->sum('subtotal_harga');
            $amount -= $amount * 0.5; // potong DP 50%

            $paymentMethod = PaymentMethod::from($validated['payment_method']);
            $paymentData = [
                'id' => Numeric::generateId('payments'),
                'id_pemesanan' => $order->id,
                'jumlah' => $amount,
                'metode' => $paymentMethod,
                'status' => PaymentStatus::Pending,
                'waktu' => $datetime,
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

                $paymentData['id_transaksi'] = $response->transaction_id ?? null;
                $paymentData['akun_virtual'] = $response->va_numbers[0]->va_number ?? null;
                $paymentData['tautan'] = $response->actions[0]->url ?? null;
            }

            $payment = $order->payments()->create($paymentData);

            return response()->json([
                'order_id' => $order->id,
                'payment' => [
                    'va_number' => $payment->akun_virtual,
                    'qr_url' => $payment->tautan,
                ],
            ]);
        });
    }
}
