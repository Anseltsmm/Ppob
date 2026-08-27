<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Format angka menjadi Rupiah.
     */
    public static function rupiah(float|int|string|null $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
