<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Helpers\Numeric;
use App\Models\Menu;
use App\Models\MenuPrice;
use App\Models\Order;
use App\Models\Table;
use App\Services\Midtrans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $menus = Menu::available()->with('prices')->get();

        return view('menu', [
            'title' => 'Menu',
            'foods' => $menus->where('category', 'food')->all(),
            'beverages' => $menus->where('category', 'beverage')->all(),
        ]);
    }

    public function checkout(): View
    {
        return view('checkout', [
            'title' => 'Checkout',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table' => 'string',
            'payment_method' => 'required|in:qris,transfer,cash',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.variant' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $order = DB::transaction(function () use ($data) {
            $datetime = now()->toDateTimeString();
            $paymentMethod = PaymentMethod::from($data['payment_method']);
            $orderMenus = [];
            $total = 0;

            foreach ($data['items'] as $item) {
                $price = MenuPrice::where('menu_id', $item['menu_id'])
                    ->when($item['variant'], fn($q) => $q->where('variant_beverage', $item['variant']))
                    ->value('price');

                if (!$price) {
                    throw new \Exception('Harga menu tidak ditemukan');
                }

                $subtotal = $price * $item['qty'];
                $total += $subtotal;

                $orderMenus[] = [
                    'menu_id' => $item['menu_id'],
                    'variant_beverage' => $item['variant'],
                    'quantity' => $item['qty'],
                    'subtotal_price' => $subtotal,
                ];
            }

            $tableId = null;

            if ($data['table']) {
                $tableId = Table::where('number', $data['table'])->value('id');
            }

            $order = Order::create([
                'id' => Numeric::generateId('orders'),
                'table_id' => $tableId,
                'datetime' => $datetime,
                'method' => $paymentMethod,
            ]);

            $order->orderMenus()->createMany($orderMenus);

            $payment = [
                'id' => Numeric::generateId('payments'),
                'datetime' => $datetime,
                'amount' => $total,
                'method' => $paymentMethod,
            ];

            if ($paymentMethod === PaymentMethod::Cash) {
                $payment['status'] = PaymentStatus::Paid;
            } else {
                $midtrans = new Midtrans();

                $payload = [
                    'payment_type' => $paymentMethod === PaymentMethod::Qris ? 'gopay' : 'bank_transfer',
                    'transaction_details' => [
                        'order_id' => $order->id,
                        'gross_amount' => $total,
                    ],
                ];

                if ($paymentMethod === PaymentMethod::Transfer) {
                    $payload['bank_transfer'] = [
                        'bank' => 'bca',
                    ];
                }

                $response = $midtrans->createTransaction($payload);

                $payment['transaction_id'] = $response->transaction_id ?? null;
                $payment['va_number'] = $response->va_numbers[0]->va_number ?? null;
                $payment['qr_url'] = $response->actions[0]->url ?? null;
            }

            $paymentRecord = $order->payments()->create($payment);

            return [
                'order' => $order,
                'payment' => $paymentRecord,
            ];
        });

        return response()->json([
            'order_id' => $order['order']->id,
            'payment' => [
                'qr_url' => $order['payment']->qr_url,
                'va_number' => $order['payment']->va_number,
            ],
        ]);
    }

    public function uploadProof(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_proof' => 'required|image|max:2048',
        ]);

        $order = Order::with('payments')->findOrFail($validated['order_id']);
        $latestPayment = $order->payments()->orderByDesc('datetime')->first();

        if (!$latestPayment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $path = $request->file('payment_proof')->store('proofs', 'r2');

        $latestPayment->update(['proof' => $path]);

        return response()->json(['message' => 'Payment proof uploaded successfully']);
    }
}
