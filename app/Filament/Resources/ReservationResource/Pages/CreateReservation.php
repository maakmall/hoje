<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $orderMenus = $data['order_menus'] ?? [];
        unset($data['order_menus']);

        $reservation = static::getModel()::create($data);
        $reservation->order()->create([
            'user_id' => $data['user_id'],
            'datetime' => $data['datetime'],
        ]);

        $reservation->order->orderMenus()->createMany($orderMenus);

        return $reservation;
    }
}
