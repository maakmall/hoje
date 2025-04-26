<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
use App\Services\Midtrans;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('payment', ['record' => $this->getRecord()]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function() use ($data): Model {
            $data['datetime'] = now()->toDateTimeString();
            $orderMenus = $data['orderMenus'] ?? [];
            $paymentMethod = PaymentMethod::from($data['payment_method']);
    
            unset($data['orderMenus']);
    
            $order = static::getModel()::create($data);
            $order->orderMenus()->createMany($orderMenus);
    
            $amount = $order->orderMenus->sum('subtotal_price');
    
            $payment = [
                'datetime' => $order->datetime,
                'amount' => $amount,
                'method' => $paymentMethod,
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
    
            $order->payments()->create($payment);
    
            return $order;
        });
    }
}
