<?php

namespace App\Support;

/**
 * Format rupiah tunggal untuk seluruh aplikasi.
 *
 * Titik sebagai pemisah ribuan, koma sebagai pemisah desimal,
 * contoh: 10000 -> Rp10.000
 */
class Rupiah
{
    public static function format(int|float|string $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}
