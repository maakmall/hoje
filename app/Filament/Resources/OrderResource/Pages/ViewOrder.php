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
use App\Services\Midtrans;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Bayar')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Bayar')
                ->visible(function (Order $record): bool {
                    return $record->payments?->where('status', PaymentStatus::Paid)->isEmpty();
                })
                ->form(function (Order $record): ?array {
                    if ($record->payments->where('status', PaymentStatus::Pending)->isNotEmpty()) {
                        return null;
                    }

                    return [
                        Placeholder::make('total')
                            ->inlineLabel()
                            ->content(function (Order $record): HtmlString {
                                $amount = $record->orderMenus->sum('subtotal_harga');

                                return new HtmlString("<span class='text-lg font-semibold'>" .
                                    Numeric::rupiah($amount, true)
                                    . "</span>");
                            }),
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options(PaymentMethod::select())
                            ->required(),
                    ];
                })
                ->action(function (Order $record, array $data): void {
                    if ($record->payments?->where('status', PaymentStatus::Pending)->isNotEmpty()) {
                        $this->redirect($this->getResource()::getUrl('payment', [
                            'record' => $record,
                            'pid' => $record->payments?->where('status', PaymentStatus::Pending)
                                ->first()?->id
                        ]));
                    } else {
                        $paymentMethod = PaymentMethod::from($data['payment_method']);
                        $amount = $record->orderMenus->sum('subtotal_harga');

                        $payment = [
                            'id' => Numeric::generateId('payments'),
                            'waktu' => now()->toDateTimeString(),
                            'jumlah' => $amount,
                            'metode' => $paymentMethod,
                            'status' => $paymentMethod === PaymentMethod::Cash
                                ? PaymentStatus::Paid
                                : PaymentStatus::Pending,
                        ];

                        if ($paymentMethod === PaymentMethod::Cash) {
                            $payment['status'] = PaymentStatus::Paid;
                        } else {
                            $midtrans = new Midtrans();

                            $payload = [
                                'payment_type' => $paymentMethod === PaymentMethod::Qris ? 'gopay' : 'bank_transfer',
                                'transaction_details' => [
                                    'order_id' => $record->id,
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

                        $paymentId = $record->payments()->create($payment)->id;

                        $this->redirect($this->getResource()::getUrl('payment', [
                            'record' => $record,
                            'pid' => $paymentId
                        ]));
                    }
                })
        ];
    }

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
                                        Infolists\Components\TextEntry::make('id_reservasi')
                                            ->label('ID Reservasi')
                                            ->badge()
                                            ->color('success')
                                            ->visible(
                                                fn(Order $record): bool => (bool) $record->id_reservasi
                                            )
                                            ->url(function (Order $record): ?string {
                                                if ($record->id_reservasi) {
                                                    return ReservationResource::getUrl('view', [
                                                        'record' => $record->id_reservasi
                                                    ]);
                                                }

                                                return null;
                                            }),
                                        Infolists\Components\TextEntry::make('waktu')
                                            ->label('Tanggal')
                                            ->dateTime('j M Y H:i'),
                                        Infolists\Components\TextEntry::make('table.nomor')
                                            ->label('Nomor Meja')
                                            ->prefix('#')
                                            ->placeholder('-')
                                            ->weight(FontWeight::Medium)
                                            ->size(TextEntrySize::Medium),
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
                                                fn(Order $record): int => $record->orderMenus->sum('subtotal_harga')
                                            ),
                                        Infolists\Components\TextEntry::make('item')
                                            ->suffix(' Pcs')
                                            ->weight(FontWeight::Medium)
                                            ->size(TextEntrySize::Medium)
                                            ->numeric(thousandsSeparator: '.')
                                            ->getStateUsing(
                                                fn(Order $record): int => $record->orderMenus->sum('jumlah')
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
                                            ->url(function (Payment $record): ?string {
                                                if ($record->status == PaymentStatus::Pending) {
                                                    return $this->getResource()::getUrl('payment', [
                                                        'record' => $record->order
                                                    ]);
                                                }

                                                return null;
                                            }),
                                        Infolists\Components\TextEntry::make('metode')
                                            ->label('Metode Pembayaran')
                                            ->formatStateUsing(
                                                fn(PaymentMethod $state): string => $state->label()
                                            ),
                                        Infolists\Components\TextEntry::make('jumlah')
                                            ->label('Nominal')
                                            ->prefix('Rp ')
                                            ->numeric(thousandsSeparator: '.'),
                                        Infolists\Components\ImageEntry::make('link')
                                            ->label('Bukti Pembayaran')
                                            ->placeholder('-')
                                            ->disk('r2')
                                            ->columnSpanFull(),
                                    ])
                            ])
                    ]),
                Infolists\Components\Section::make('Menu')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('orderMenus')
                            ->hiddenLabel()
                            ->columns(7)
                            ->schema([
                                Infolists\Components\TextEntry::make('menu.nama')
                                    ->columnSpan(function (OrderMenu $record): int {
                                        return $record->variasi_minuman ? 3 : 4;
                                    }),
                                Infolists\Components\TextEntry::make('variasi_minuman')
                                    ->label('Varian')
                                    ->hidden(
                                        fn(OrderMenu $record): bool => !$record->variasi_minuman
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
                                                ->where('variasi_minuman', $record->variasi_minuman)->first()->harga;

                                            return Numeric::rupiah($price);
                                        }
                                    ),
                                Infolists\Components\TextEntry::make('jumlah')
                                    ->label('Jumlah'),
                                Infolists\Components\TextEntry::make('subtotal_harga')
                                    ->label('Subtotal')
                                    ->prefix('Rp ')
                                    ->numeric(thousandsSeparator: '.'),
                            ])
                    ])
            ]);
    }
}
