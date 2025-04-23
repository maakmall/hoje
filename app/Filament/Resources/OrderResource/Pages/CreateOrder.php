<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['datetime'] = now()->toDateTimeString();
        $orderMenus = $data['orderMenus'] ?? [];
        $paymentMethod = PaymentMethod::from($data['payment_method']);

        unset($data['orderMenus']);

        $order = static::getModel()::create($data);
        $order->orderMenus()->createMany($orderMenus);

        $payment['datetime'] = $order->datetime;
        $payment['amount'] = $order->orderMenus->sum('subtotal_price');
        $payment['method'] = match ($paymentMethod) {
            PaymentMethod::Cash => PaymentMethod::Cash,
            PaymentMethod::Qris => PaymentMethod::Qris,
            PaymentMethod::Transfer => PaymentMethod::Transfer,
        };

        if ($paymentMethod == PaymentMethod::Cash) {
            $payment['status'] = PaymentStatus::Paid;
        } else if ($paymentMethod == PaymentMethod::Qris) {
            
        } else {

        }

        $order->payments()->create($payment);

        return $order;
    }
}
