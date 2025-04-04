<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Enums\VariantBeverage;
use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $price = $data['price'];
        $hasVariant = $data['has_variant'] ?? false;
        unset($data['price'], $data['has_variant']);

        if ($hasVariant) {
            $priceCold = $data['price_cold'];
            unset($data['price_cold']);

            $menu = static::getModel()::create($data);
            $menu->prices()->createMany([
                [
                    'price' => $price,
                    'variant_beverage' => VariantBeverage::Hot,
                ],
                [
                    'price' => $priceCold,
                    'variant_beverage' => VariantBeverage::Cold,
                ]
            ]);
        } else {
            $menu = static::getModel()::create($data);
            $menu->prices()->create([
                'price' => $price,
            ]);
        }

        return $menu;
    }
}
