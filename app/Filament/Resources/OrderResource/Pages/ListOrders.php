<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('report')
                ->label('Laporan')
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->modalHeading('Buat Laporan')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Buat')
                ->form([
                    DatePicker::make('start')
                        ->label('Tanggal Awal')
                        ->required()
                        ->lte('end'),
                    DatePicker::make('end')
                        ->label('Tanggal Akhir')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $orders = Order::whereDate('waktu', '>=', $data['start'])
                        ->whereDate('waktu', '<=', $data['end'])
                        ->withSum('orderMenus', 'subtotal_harga')
                        ->withSum('orderMenus', 'jumlah')
                        ->whereHas('payments', function(Builder $query): void {
                            $query->where('status', PaymentStatus::Paid);
                        })
                        ->orderBy('waktu')
                        ->get();

                    $start = Carbon::parse($data['start']);
                    $end = Carbon::parse($data['end']);

                    $pdf = Pdf::loadView('order-report', [
                        'orders' => $orders,
                        'start' => $start,
                        'end' => $end,
                        'total' => $orders->sum('order_menus_sum_subtotal_harga'),
                        'total_items' => $orders->sum('order_menus_sum_jumlah'),
                    ]);

                    $filename = 'laporan-pesanan-' . $start->format('d-m-Y') . '-' . $end->format('d-m-Y') . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, $filename);
                })
        ];
    }
}
