<?php

namespace App\Enums;

enum VariantBeverage: string
{
    case Hot = 'hot';
    case Cold = 'cold';

    /**
     * Get the variant beverage values
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
