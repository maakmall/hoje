<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VariantBeverage;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ReservationResource;
use App\Helpers\Numeric;
use App\Models\Menu;
use App\Models\OrderMenu;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\Midtrans;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Actions;
use Filament\Forms;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('finish_payment')
                ->label('Selesaikan Pembayaran')
                ->visible(function (Reservation $record): bool {
                    $total = $record->order->orderMenus->sum('subtotal_harga');
                    $amount = $record->order->payments
                        ->where('status', PaymentStatus::Paid)
                        ->sum('jumlah');

                    return $amount !== $total && $amount !== 0;
                })
                ->modalWidth(MaxWidth::Medium)
                ->form([
                    Forms\Components\Placeholder::make('total')
                        ->inlineLabel()
                        ->content(function (Reservation $record): HtmlString {
                            $amount = $record->order->payments
                                ->where('status', PaymentStatus::Paid)
                                ->sum('jumlah');

                            if ($amount == 0) {
                                $amount = $record->order->orderMenus->sum('subtotal_harga');
                            } else {
                                $amount = $record->order->orderMenus->sum('subtotal_harga') - $amount;
                            }

                            return new HtmlString("<span class='text-lg font-semibold'>" .
                                Numeric::rupiah($amount, true)
                                . "</span>");
                        }),
                    Forms\Components\Radio::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->required()
                        ->options(PaymentMethod::select())
                        ->inline()
                        ->default('cash')
                        ->inlineLabel(false),
                ])
                ->modalSubmitActionLabel('Bayar')
                ->action(function (Reservation $record, array $data): void {
                    $paymentMethod = PaymentMethod::from($data['payment_method']);
                    $amount = $record->order->payments
                        ->where('status', PaymentStatus::Paid)
                        ->sum('jumlah');

                    if ($amount == 0) {
                        $amount = $record->order->orderMenus->sum('subtotal_harga');
                    } else {
                        $amount = $record->order->orderMenus->sum('subtotal_harga') - $amount;
                    }

                    $payment = [
                        'id' => Numeric::generateId('payments'),
                        'waktu' => $record->order->waktu,
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
                                'order_id' => $record->order->id,
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

                    $payment = $record->order->payments()->create($payment);

                    $this->redirect(OrderResource::getUrl('payment', [
                        'record' => $record->order,
                        'redirect' => 'reservation',
                        'pid' => $payment->id
                    ]));
                }),
            Actions\Action::make('Bayar')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Bayar')
                ->visible(function (Reservation $record): bool {
                    $total = $record->order->orderMenus->sum('subtotal_harga');
                    $amount = $record->order->payments
                        ->where('status', PaymentStatus::Paid)
                        ->sum('jumlah');

                    return $amount !== $total && $amount == 0;
                })
                ->form(function (Reservation $record): ?array {
                    if ($record->order->payments->where('status', PaymentStatus::Pending)->isNotEmpty()) {
                        return null;
                    }

                    return [
                        Forms\Components\Placeholder::make('total')
                            ->inlineLabel()
                            ->content(function (Reservation $record): HtmlString {
                                $amount = $record->orderMenus->sum('subtotal_harga');
                                $amount -= $amount * 50 / 100;

                                return new HtmlString("<span class='text-lg font-semibold'>" .
                                    Numeric::rupiah($amount, true)
                                    . "</span>");
                            })
                            ->helperText(
                                'Pembayaran DP 50% dari total harga reservasi.'
                            ),
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options(PaymentMethod::select())
                            ->required(),
                    ];
                })
                ->action(function (Reservation $record, array $data): void {
                    if ($record->order->payments?->where('status', PaymentStatus::Pending)->isNotEmpty()) {
                        $this->redirect(OrderResource::getUrl('payment', [
                            'record' => $record->order->id,
                            'pid' => $record->order->payments?->where('status', PaymentStatus::Pending)
                                ->first()?->id,
                            'redirect' => 'reservation'
                        ]));
                    } else {
                        $paymentMethod = PaymentMethod::from($data['payment_method']);
                        $amount = $record->orderMenus->sum('subtotal_harga');
                        $amount -= $amount * 50 / 100;

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
                                    'order_id' => $record->order->id . 'DP',
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

                        $paymentId = $record->order->payments()->create($payment)->id;

                        $this->redirect(OrderResource::getUrl('payment', [
                            'record' => $record->order->id,
                            'redirect' => 'reservation',
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
                        Infolists\Components\Tabs\Tab::make('Reservasi')
                            ->columns(3)
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->columnSpan(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('id')
                                            ->label('ID Reservasi'),
                                        Infolists\Components\TextEntry::make('waktu')
                                            ->label('Tanggal')
                                            ->dateTime('j M Y H:i'),
                                        Infolists\Components\TextEntry::make('location.nama')
                                            ->label('Lokasi'),
                                    ]),
                                Infolists\Components\Section::make()
                                    ->columns()
                                    ->columnSpan(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nama_pelanggan')
                                            ->label('Pelanggan'),
                                        Infolists\Components\TextEntry::make('status')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->getStateUsing(function (Reservation $record): string {
                                                $total = $record->order->orderMenus->sum('subtotal_harga');
                                                $amount = $record->order->payments
                                                    ->where('status', PaymentStatus::Paid)
                                                    ->sum('jumlah');

                                                if ($amount == 0) {
                                                    return 'Belum Bayar';
                                                }

                                                return $amount !== $total
                                                    ? 'DP'
                                                    : 'Dibayar';
                                            }),
                                        Infolists\Components\TextEntry::make('email_pelanggan')
                                            ->label('Email'),
                                        Infolists\Components\TextEntry::make('telepon_pelanggan')
                                            ->label('Telepon'),
                                        Infolists\Components\TextEntry::make('jumlah_orang')
                                            ->label('Jumlah Orang')
                                            ->suffix(' Orang'),
                                    ]),
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('catatan')
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
                            ]),
                        Infolists\Components\Tabs\Tab::make('Pembayaran')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('order.payments')
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
                                                    return OrderResource::getUrl('payment', [
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
                                            ->columnSpanFull(),
                                    ])
                            ])
                    ]),
            ]);
    }
}
