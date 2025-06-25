<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Helpers\Numeric;
use App\Services\Midtrans;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;
    protected string $paymentId;

    protected function getRedirectUrl(): string
    {
        return OrderResource::getUrl('payment', [
            'record' => $this->getRecord()->order->id,
            'redirect' => 'reservation',
            'pid' => $this->paymentId,
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function() use ($data): Model {
            $orderMenus = $data['orderMenus'] ?? [];
            $paymentMethod = PaymentMethod::from($data['payment_method']);
            
            unset($data['orderMenus']);

            $data['id'] = Numeric::generateId('reservations');
    
            $reservation = static::getModel()::create($data);
            $order = $reservation->order()->create([
                'id' => Numeric::generateId('orders'),
                'waktu' => $data['waktu'],
            ]);
    
            $order->orderMenus()->createMany($orderMenus);

            $amount = $order->orderMenus->sum('subtotal_harga');
            $amount -= $amount * 50 / 100;

            // if ($data['payment_type'] == 'dp') {
            //     $amount -= $amount * 50 / 100;
            // }
    
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
    
                $payment['id_transaksi'] = $response->transaction_id ?? null;
                $payment['akun_virtual'] = $response->va_numbers[0]->va_number ?? null;
                $payment['tautan'] = $response->actions[0]->url ?? null;
            }
    
            $this->paymentId = $order->payments()->create($payment)->id;
    
            return $reservation;
        });
    }
}
