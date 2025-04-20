<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Enums\VariantBeverage;
use App\Filament\Resources\ReservationResource;
use App\Helpers\Numeric;
use App\Models\Menu;
use App\Models\OrderMenu;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Reservasi')
                            ->columns(3)
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->columnSpan(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('id')
                                            ->label('ID Reservasi'),
                                        Infolists\Components\TextEntry::make('datetime')
                                            ->label('Tanggal')
                                            ->dateTime('j M Y H:i'),
                                    ]),
                                Infolists\Components\Section::make()
                                    ->columns()
                                    ->columnSpan(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('user.name')
                                            ->label('Pelanggan')
                                            ->placeholder('-')
                                            ->columnSpanFull(),
                                        Infolists\Components\TextEntry::make('location.name')
                                            ->label('Lokasi'),
                                        Infolists\Components\TextEntry::make('number_of_people')
                                            ->label('Jumlah Orang')
                                            ->suffix(' Orang'),
                                    ]),
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Catatan')
                                            ->placeholder('-')
                                            ->columnSpanFull(),
                                    ]),
                                Infolists\Components\Section::make('Menu')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('orderMenus')
                                            ->hiddenLabel()
                                            ->columns(7)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('menu.name')
                                                    ->columnSpan(function (OrderMenu $record): int {
                                                        return $record->variant_beverage ? 3 : 4;
                                                    }),
                                                Infolists\Components\TextEntry::make('variant_beverage')
                                                    ->label('Varian')
                                                    ->hidden(
                                                        fn(OrderMenu $record): bool => !$record->variant_beverage
                                                    )
                                                    ->formatStateUsing(
                                                        fn(?VariantBeverage $state): string => $state->name
                                                    ),
                                                Infolists\Components\TextEntry::make('menu')
                                                    ->label('Harga')
                                                    ->prefix('Rp ')
                                                    ->formatStateUsing(
                                                        function (Menu $state, OrderMenu $record): string {
                                                            $price = $state->prices
                                                                ->where('variant_beverage', $record->variant_beverage)->first()->price;

                                                            return Numeric::rupiah($price);
                                                        }
                                                    ),
                                                Infolists\Components\TextEntry::make('quantity')
                                                    ->label('Jumlah'),
                                                Infolists\Components\TextEntry::make('subtotal_price')
                                                    ->label('Subtotal')
                                                    ->prefix('Rp ')
                                                    ->numeric(thousandsSeparator: '.'),
                                            ])
                                    ])
                            ]),
                        Infolists\Components\Tabs\Tab::make('Pembayaran')
                            ->schema([])
                    ]),
            ]);
    }
}
