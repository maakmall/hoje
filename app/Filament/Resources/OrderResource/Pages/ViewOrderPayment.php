<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ReservationResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

class ViewOrderPayment extends ViewRecord
{
    protected static string $resource = OrderResource::class;
    protected static ?string $title = 'Bayar';
    protected static ?string $breadcrumb = 'Bayar';
    protected Payment $payment;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('finish')
                ->label('Selesai')
                ->url(function (Request $request): string {
                    return $request->query('redirect') == 'reservation'
                        ? ReservationResource::getUrl('index')
                        : $this->getResource()::getUrl('index');
                }),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (request('pid')) {
            $this->payment = $this->record->payments->where('id', request('pid'))->first();
        } else {
            $this->payment = $this->record->payments->first();
        }

        if ($this->payment->status != PaymentStatus::Pending) {
            if (request('redirect') == 'reservation') {
                $this->redirect(ReservationResource::getUrl('view', [
                    'record' => $this->record->reservation
                ]));
            } else {
                $this->redirect($this->getResource()::getUrl('view', [
                    'record' => $this->record
                ]));
            }
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
                            ->getStateUsing(fn(): string => $this->payment->method->label()),
                        Infolists\Components\TextEntry::make('va_number')
                            ->hiddenLabel()
                            ->alignCenter()
                            ->visible(fn(): bool => (bool) $this->payment->va_number)
                            ->size(TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->getStateUsing(
                                fn(): ?HtmlString => new HtmlString("
                                    <span class='text-2xl' style='letter-spacing: 3px;'>
                                        VA : {$this->payment->va_number}
                                    </span>
                                ")
                            ),
                        Infolists\Components\ImageEntry::make('qr')
                            ->hiddenLabel()
                            ->width(300)
                            ->height(300)
                            ->alignCenter()
                            ->visible(fn(): bool => (bool) $this->payment->qr_url)
                            ->getStateUsing(fn(): ?string => $this->payment->qr_url),
                        Infolists\Components\TextEntry::make('payment')
                            ->hiddenLabel()
                            ->prefix('Rp ')
                            ->numeric(thousandsSeparator: '.')
                            ->alignCenter()
                            ->size(TextEntrySize::Large)
                            ->weight(FontWeight::SemiBold)
                            ->getStateUsing(fn(): float => $this->payment->amount)
                    ])
            ]);
    }
}
