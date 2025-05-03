<?php

namespace App\Filament\Widgets;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MenuOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $menus = Menu::select('id', 'category')->get();
        $foods = $menus->where('category', 'food')->count();
        $beverage = $menus->where('category', 'beverage')->count();

        $reservations = Reservation::whereDate('datetime', now()->toDateString())->count();

        return [
            Stat::make('Reservasi', $reservations)
                ->label('Jumlah Reservasi')
                ->icon('heroicon-o-calendar')
                ->description('Reservasi hari ini'),
            Stat::make('Pesanan', Order::count())
                ->label('Jumlah Pesanan')
                ->icon('heroicon-o-shopping-cart')
                ->description('Total seluruh pesanan'),
            Stat::make('Menu', $menus->count())
                ->label('Jumlah Menu')
                ->icon('heroicon-o-book-open')
                ->description("$foods makanan dan $beverage minuman"),
        ];
    }
}
