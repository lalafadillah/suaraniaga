<?php

namespace App\Support;

/**
 * Normalisasi angka yang diketik atau diucapkan pengguna.
 *
 * Hasil dikte suara sering berupa "72.000", "Rp 72.000", atau "72 ribu".
 * Pola seperti itu harus tetap bisa disimpan, sedangkan teks yang bukan angka
 * harus dibiarkan apa adanya supaya validasi tetap menolaknya.
 */
class Harga
{
    /**
     * Ubah "Rp 72.000" atau "72 ribu" menjadi angka rupiah.
     *
     * Nilai yang tidak dikenali dikembalikan apa adanya supaya aturan
     * validasi tetap bisa menolaknya.
     */
    public static function rupiah(mixed $nilai): mixed
    {
        $teks = trim((string) $nilai);

        if ($teks === '') {
            return $nilai;
        }

        $pola = '/^(?:rp\.?\s*)?(\d{1,3}(?:[.,\s]\d{3})*|\d+)\s*(ribu|juta)?$/iu';

        if (! preg_match($pola, $teks, $cocok)) {
            return $nilai;
        }

        $angka = (int) str_replace(['.', ',', ' '], '', $cocok[1]);

        return match (mb_strtolower($cocok[2] ?? '')) {
            'ribu' => $angka * 1000,
            'juta' => $angka * 1_000_000,
            default => $angka,
        };
    }

    /**
     * Ubah "24" menjadi angka. Teks lain dibiarkan agar validasi menolak.
     */
    public static function bilangan(mixed $nilai): mixed
    {
        $teks = trim((string) $nilai);

        if ($teks === '') {
            return $nilai;
        }

        return preg_match('/^\d+$/', $teks) ? (int) $teks : $nilai;
    }
}
