<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Enums\MenuCategory;
use App\Filament\Resources\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            MenuCategory::Food->value => Tab::make(MenuCategory::Food->label())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('category', MenuCategory::Food)),
            MenuCategory::Beverage->value => Tab::make(MenuCategory::Beverage->label())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('category', MenuCategory::Beverage)),
        ];
    }
}
