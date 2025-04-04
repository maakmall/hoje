<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Enums\VariantBeverage;
use App\Filament\Resources\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $price = $data['price'];
        $hasVariant = $data['has_variant'] ?? false;
        unset($data['price'], $data['has_variant']);

        $record->prices()->delete();

        if ($hasVariant) {
            $priceCold = $data['price_cold'];
            unset($data['price_cold']);

            $record->prices()->createMany([
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
            $record->prices()->create([
                'price' => $price,
            ]);
        }

        $record->update($data);

        return $record;
    }
}
