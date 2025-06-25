<?php

namespace App\Filament\Resources\MenuResource\Widgets;

use App\Enums\MenuCategory;
use App\Helpers\Numeric;
use App\Models\Menu;
use App\Models\MenuPrice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MenuOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        return [
            Stat::make(
                MenuCategory::Food->label(),
                Menu::food()->count()
            )
                ->icon('heroicon-o-tag'),
            Stat::make(
                MenuCategory::Beverage->label(),
                Menu::beverage()->count()
            )
                ->icon('heroicon-o-tag'),
            Stat::make(
                'Rata-rata',
                Numeric::rupiah(MenuPrice::avg('harga') ?? 0, true)
            )
                ->icon('heroicon-o-banknotes')
                ->description('Harga rata-rata menu')
                ->descriptionColor('primary')
        ];
    }
}
