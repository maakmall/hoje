<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VariantBeverage;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ReservationResource;
use App\Helpers\Numeric;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderMenu;
use App\Models\Payment;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Pesanan')
                            ->columns(3)
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->columnSpan(2)
                                    ->columns()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('id')
                                            ->label('ID Pesanan')
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('reservation_id')
                                            ->label('ID Reservasi')
                                            ->badge()
                                            ->color('success')
                                            ->visible(
                                                fn(Order $record): bool => (bool) $record->reservation_id
                                            )
                                            ->url(function (Order $record): ?string {
                                                if ($record->reservation_id) {
                                                    return ReservationResource::getUrl('view', [
                                                        'record' => $record->reservation_id
                                                    ]);
                                                }

                                                return null;
                                            }),
                                        Infolists\Components\TextEntry::make('datetime')
                                            ->label('Tanggal')
                                            ->dateTime('j M Y H:i'),
                                        Infolists\Components\TextEntry::make('user.name')
                                            ->label('Pelanggan')
                                            ->placeholder('-'),
                                    ]),
                                Infolists\Components\Section::make()
                                    ->columnSpan(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('total')
                                            ->prefix('Rp ')
                                            ->weight(FontWeight::SemiBold)
                                            ->size(TextEntrySize::Large)
                                            ->numeric(thousandsSeparator: '.')
                                            ->getStateUsing(
                                                fn(Order $record): int => $record->orderMenus->sum('subtotal_price')
                                            ),
                                        Infolists\Components\TextEntry::make('item')
                                            ->suffix(' Pcs')
                                            ->weight(FontWeight::Medium)
                                            ->size(TextEntrySize::Medium)
                                            ->numeric(thousandsSeparator: '.')
                                            ->getStateUsing(
                                                fn(Order $record): int => $record->orderMenus->sum('quantity')
                                            ),
                                    ]),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Pembayaran')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('payments')
                                    ->hiddenLabel()
                                    ->columns()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('id')
                                            ->label('ID Pembayaran'),
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->formatStateUsing(
                                                fn(PaymentStatus $state): string => $state->label()
                                            )
                                            ->url(function(Payment $record): ?string {
                                                if ($record->status == PaymentStatus::Pending) {
                                                    return $this->getResource()::getUrl('payment', [
                                                        'record' => $record->order
                                                    ]);
                                                }

                                                return null;
                                            }),
                                        Infolists\Components\TextEntry::make('method')
                                            ->label('Metode Pembayaran')
                                            ->formatStateUsing(
                                                fn(PaymentMethod $state): string => $state->label()
                                            ),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->label('Nominal')
                                            ->prefix('Rp ')
                                            ->numeric(thousandsSeparator: '.'),
                                    ])
                            ])
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
            ]);
    }
}
