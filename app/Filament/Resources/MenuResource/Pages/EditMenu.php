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
                    'harga' => $price,
                    'variasi_minuman' => VariantBeverage::Hot,
                ],
                [
                    'harga' => $priceCold,
                    'variasi_minuman' => VariantBeverage::Cold,
                ]
            ]);
        } else {
            $record->prices()->create([
                'harga' => $price,
            ]);
        }

        $record->update($data);

        return $record;
    }
}
