<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\CoreApi;

class Midtrans
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(array $payload): mixed
    {
        return CoreApi::charge($payload);
    }
}
