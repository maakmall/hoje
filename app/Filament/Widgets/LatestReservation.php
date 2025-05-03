<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestReservation extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->with(['order.orderMenus', 'order.payments'])
                    ->whereDate('datetime', now()->toDateString())
            )
            ->recordUrl(
                fn(Reservation $record): string => ReservationResource::getUrl('view', ['record' => $record])
            )
            ->paginated(false)
            ->heading('Reservasi Hari Ini')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan'),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi'),
                Tables\Columns\TextColumn::make('number_of_people')
                    ->label('Jumlah Orang')
                    ->suffix(' Orang')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function (Reservation $record): string {
                        $total = $record->order->orderMenus->sum('subtotal_price');
                        $amount = $record->order->payments
                            ->where('status', PaymentStatus::Paid)
                            ->sum('amount');

                        if ($amount == 0) {
                            return 'Belum Bayar';
                        }

                        return $amount !== $total
                            ? 'DP'
                            : 'Dibayar';
                    }),
            ]);
    }
}
