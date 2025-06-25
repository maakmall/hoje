<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
use App\Helpers\Numeric;
use App\Services\Midtrans;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected static bool $canCreateAnother = false;
    protected ?string $paymentId;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('payment', [
            'record' => $this->getRecord(),
            'pid' => $this->paymentId
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function() use ($data): Model {
            $data['waktu'] = now()->toDateTimeString();
            $orderMenus = $data['orderMenus'] ?? [];
            $paymentMethod = PaymentMethod::from($data['payment_method']);
    
            unset($data['orderMenus']);
    
            $order = static::getModel()::create($data);
            $order->orderMenus()->createMany($orderMenus);
    
            $amount = $order->orderMenus->sum('subtotal_harga');
    
            $payment = [
                'id' => Numeric::generateId('payments'),
                'waktu' => $order->waktu,
                'jumlah' => $amount,
                'metode' => $paymentMethod,
                'status' => PaymentStatus::Pending,
            ];
    
            if ($paymentMethod === PaymentMethod::Cash) {
                $payment['status'] = PaymentStatus::Paid;
            } else {
                $midtrans = new Midtrans();
    
                $payload = [
                    'payment_type' => $paymentMethod === PaymentMethod::Qris ? 'gopay' : 'bank_transfer',
                    'transaction_details' => [
                        'order_id' => $order->id,
                        'gross_amount' => $amount,
                    ],
                    'custom_expiry' => [
                        'unit' => 'day',
                        'expiry_duration' => 1,
                    ],
                ];
    
                if ($paymentMethod === PaymentMethod::Transfer) {
                    $payload['bank_transfer'] = [
                        'bank' => 'bca',
                    ];
                }
    
                $response = $midtrans->createTransaction($payload);
    
                $payment['id_transaksi'] = $response->transaction_id ?? null;
                $payment['akun_virtual'] = $response->va_numbers[0]->va_number ?? null;
                $payment['tautan'] = $response->actions[0]->url ?? null;
            }
    
            $this->paymentId = $order->payments()->create($payment)->id;
    
            return $order;
        });
    }
}
