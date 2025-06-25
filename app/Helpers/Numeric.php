<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    /**
     * Generate ID for orders, reservations and payment
     * with format [PREFIX][DATE][ORDERNUMBER]
     * 
     * Example: P29042025001
     */
    public static function generateId(string $table): string
    {
        $date = Carbon::now()->format('dmY');
        $prefix = match ($table) {
            'orders' => 'O',
            'reservations' => 'R',
            'payments' => 'P',
        };
        
        $table = match ($table) {
            'orders' => 'pemesanan',
            'reservations' => 'reservasi',
            'payments' => 'pembayaran',
        };

        $prefix .= $date;

        $lastId = DB::table($table)
            ->whereRaw('LEFT(id, ?) = ?', [strlen($prefix), $prefix])
            ->orderByDesc('id')
            ->value('id');

        if ($lastId) {
            $lastNumber = (int) substr($lastId, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }
}
