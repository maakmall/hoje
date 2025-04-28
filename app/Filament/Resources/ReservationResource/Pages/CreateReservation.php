<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Services\Midtrans;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getRedirectUrl(): string
    {
        return OrderResource::getUrl('payment', [
            'record' => $this->getRecord()->order->id,
            'redirect' => 'reservation'
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function() use ($data): Model {
            $orderMenus = $data['orderMenus'] ?? [];
            $paymentMethod = PaymentMethod::from($data['payment_method']);
            
            unset($data['orderMenus']);
    
            $reservation = static::getModel()::create($data);
            $order = $reservation->order()->create([
                'user_id' => $data['user_id'],
                'datetime' => $data['datetime'],
            ]);
    
            $order->orderMenus()->createMany($orderMenus);

            $amount = $order->orderMenus->sum('subtotal_price');

            if ($data['payment_type'] == 'dp') {
                $amount -= $amount * 50 / 100;
            }
    
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
                        'order_id' => $order->id . 'DP',
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
    
            return $reservation;
        });
    }
}
