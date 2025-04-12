<?php

namespace App\Helpers;

class Numeric
{
    /**
     * Format number to Indonesian Rupiah
     */
    public static function rupiah(int $nominal, bool $withRp = false): string
    {
        return $withRp
            ? 'Rp ' . number_format($nominal, 0, '.', '.')
            : number_format($nominal, 0, '.', '.');
    }
}
