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
                    $total = $record->order->orderMenus->sum('subtotal_price');
                    $amount = $record->order->payments
                        ->where('status', PaymentStatus::Paid)
                        ->sum('amount');

                    return $amount !== $total;
                })
                ->modalWidth(MaxWidth::Medium)
                ->form([
                    Forms\Components\Placeholder::make('total')
                        ->inlineLabel()
                        ->content(function (Reservation $record): HtmlString {
                            $amount = $record->order->payments
                                ->where('status', PaymentStatus::Paid)
                                ->sum('amount');

                            if ($amount == 0) {
                                $amount = $record->order->orderMenus->sum('subtotal_price');
                            } else {
                                $amount = $record->order->orderMenus->sum('subtotal_price') - $amount;
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
                        ->sum('amount');

                    if ($amount == 0) {
                        $amount = $record->order->orderMenus->sum('subtotal_price');
                    } else {
                        $amount = $record->order->orderMenus->sum('subtotal_price') - $amount;
                    }

                    $payment = [
                        'id' => Numeric::generateId('payments'),
                        'datetime' => $record->order->datetime,
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

                        $payment['transaction_id'] = $response->transaction_id ?? null;
                        $payment['va_number'] = $response->va_numbers[0]->va_number ?? null;
                        $payment['qr_url'] = $response->actions[0]->url ?? null;
                    }

                    $payment = $record->order->payments()->create($payment);

                    $this->redirect(OrderResource::getUrl('payment', [
                        'record' => $record->order,
                        'redirect' => 'reservation',
                        'pid' => $payment->id
                    ]));
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
                                        Infolists\Components\TextEntry::make('datetime')
                                            ->label('Tanggal')
                                            ->dateTime('j M Y H:i'),
                                    ]),
                                Infolists\Components\Section::make()
                                    ->columns()
                                    ->columnSpan(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('customer_name')
                                            ->label('Pelanggan'),
                                        Infolists\Components\TextEntry::make('status')
                                            ->hiddenLabel()
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
            ]);
    }
}
