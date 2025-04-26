<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class ViewOrderPayment extends ViewRecord
{
    protected static string $resource = OrderResource::class;
    protected static ?string $title = 'Bayar';
    protected static ?string $breadcrumb = 'Bayar';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('finish')
                ->label('Selesai')
                ->url(function(): string {
                    return $this->getResource()::getUrl('index');
                }),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $payment = $this->record->payments->first();
        
        if ($payment->status != PaymentStatus::Pending) {
            $this->redirect($this->getResource()::getUrl('view', [
                'record' => $this->record
            ]));
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_method')
                            ->hiddenLabel()
                            ->alignCenter()
                            ->size(TextEntrySize::Large)
                            ->weight(FontWeight::SemiBold)
                            ->getStateUsing(function (Order $record): string {
                                return $record->payments->first()->method->label();
                            }),
                        Infolists\Components\TextEntry::make('va_number')
                            ->hiddenLabel()
                            ->alignCenter()
                            ->visible(
                                fn(Order $record): bool => (bool) $record->payments->first()->va_number
                            )
                            ->size(TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->getStateUsing(function (Order $record): ?HtmlString {
                                $va = $record->payments->first()->va_number;
                                return new HtmlString("
                                    <span class='text-2xl' style='letter-spacing: 3px;'>VA : $va</span>
                                ");
                            }),
                        Infolists\Components\ImageEntry::make('qr')
                            ->hiddenLabel()
                            ->width(300)
                            ->height(300)
                            ->alignCenter()
                            ->visible(
                                fn(Order $record): bool => (bool) $record->payments->first()->qr_url
                            )
                            ->getStateUsing(function (Order $record): ?string {
                                return $record->payments->first()->qr_url;
                            }),
                        Infolists\Components\TextEntry::make('payment')
                            ->hiddenLabel()
                            ->prefix('Rp ')
                            ->numeric(thousandsSeparator: '.')
                            ->alignCenter()
                            ->size(TextEntrySize::Large)
                            ->weight(FontWeight::SemiBold)
                            ->getStateUsing(function (Order $record): float {
                                return $record->payments->first()->amount;
                            })
                    ])
            ]);
    }
}
