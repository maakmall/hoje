<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Helpers\Numeric;
use App\Mail\ReservationConfirmation;
use App\Models\Menu;
use App\Models\MenuPrice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Services\Midtrans;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $menus = Menu::available()->with('prices')->get();

        return view('menu', [
            'title' => 'Menu',
            'foods' => $menus->where('kategori', 'food')->all(),
            'beverages' => $menus->where('kategori', 'beverage')->all(),
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
            'table' => 'nullable|integer|exists:meja,nomor',
            // 'payment_method' => 'required|in:qris,transfer,cash',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.variant' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $order = DB::transaction(function () use ($data) {
            $datetime = now()->toDateTimeString();
            // $paymentMethod = PaymentMethod::from($data['payment_method']);
            $orderMenus = [];
            $total = 0;

            foreach ($data['items'] as $item) {
                $price = MenuPrice::where('id_menu', $item['menu_id'])
                    ->when($item['variant'], fn($q) => $q->where('variasi_minuman', $item['variant']))
                    ->value('harga');

                if (!$price) {
                    throw new \Exception('Invalid menu or variant selected.');
                }

                $subtotal = $price * $item['qty'];
                $total += $subtotal;

                $orderMenus[] = [
                    'id_menu' => $item['menu_id'],
                    'variasi_minuman' => $item['variant'],
                    'jumlah' => $item['qty'],
                    'subtotal_harga' => $subtotal,
                ];
            }

            $tableId = null;

            if ($data['table']) {
                $tableId = Table::where('nomor', $data['table'])->value('id');
            }

            $order = Order::create([
                'id' => Numeric::generateId('orders'),
                'id_meja' => $tableId,
                'waktu' => $datetime,
            ]);

            $order->orderMenus()->createMany($orderMenus);

            // $payment = [
            //     'id' => Numeric::generateId('payments'),
            //     'waktu' => $datetime,
            //     'jumlah' => $total,
            //     'metode' => $paymentMethod,
            // ];

            // if ($paymentMethod === PaymentMethod::Cash) {
            //     $payment['status'] = PaymentStatus::Paid;
            // } else {
            //     $midtrans = new Midtrans();

            //     $payload = [
            //         'payment_type' => $paymentMethod === PaymentMethod::Qris ? 'gopay' : 'bank_transfer',
            //         'transaction_details' => [
            //             'order_id' => $order->id,
            //             'gross_amount' => $total,
            //         ],
            //     ];

            //     if ($paymentMethod === PaymentMethod::Transfer) {
            //         $payload['bank_transfer'] = [
            //             'bank' => 'bca',
            //         ];
            //     }

            //     $response = $midtrans->createTransaction($payload);

            //     $payment['id_transaksi'] = $response->transaction_id ?? null;
            //     $payment['akun_virtual'] = $response->va_numbers[0]->va_number ?? null;
            //     $payment['tautan'] = $response->actions[0]->url ?? null;
            // }

            // $paymentRecord = $order->payments()->create($payment);

            return [
                'order' => $order,
                // 'payment' => $paymentRecord,
            ];
        });

        return response()->json([
            'order_id' => $order['order']->id,
            // 'payment' => [
            //     'qr_url' => $order['payment']->tautan,
            //     'va_number' => $order['payment']->akun_virtual,
            // ],
        ]);
    }

    public function uploadProof(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:pemesanan,id',
            'payment_proof' => 'required|image|max:2048',
        ]);

        $order = Order::with('payments')->findOrFail($validated['order_id']);
        $latestPayment = $order->payments()->orderByDesc('waktu')->first();

        if (!$latestPayment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $path = $request->file('payment_proof')->store('proofs', 'r2');

        $latestPayment->update(['link' => $path]);

        Mail::to($order->reservation->email_pelanggan)->send(
            new ReservationConfirmation(
                $order->reservation,
                $order,
                $latestPayment,
                $order->orderMenus
            )
        );

        return response()->json(['message' => 'Payment proof uploaded successfully']);
    }

    public function payment(Request $request): Response
    {
        $serverKey = config('midtrans.server_key');
        $signatureKey = $request->input('signature_key');
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');

        // Generate signature untuk verifikasi
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        // Kalau signature nggak valid, tolak
        if ($signatureKey !== $expectedSignature) {
            return response('Invalid signature', 403);
        }

        // Cari data pembayaran berdasarkan order_id
        $payment = Payment::where('id_transaksi', $transactionId)->first();

        if (!$payment) {
            return response('Payment not found', 404);
        }

        // Mapping status dari Midtrans ke status internal
        $transactionStatus = $request->input('transaction_status');

        switch ($transactionStatus) {
            case 'settlement':
            case 'capture':
                $status = PaymentStatus::Paid;
                break;
            case 'pending':
                $status = PaymentStatus::Pending;
                break;
            case 'deny':
            case 'expire':
            case 'cancel':
                $status = PaymentStatus::Failed;
                break;
            default:
                $status = PaymentStatus::Pending;
        }

        // Update payment
        $payment->update(['status' => $status]);

        return response('OK', 200); // wajib balikin 200 biar Midtrans gak retry
    }
}
