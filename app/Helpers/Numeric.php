<?php

namespace App\Helpers;

class Numeric
{
    /**
     * Format number to Indonesian Rupiah
     */
    public static function rupiah(int $nominal): string
    {
        return number_format($nominal, 0, '.', '.');
    }
}
