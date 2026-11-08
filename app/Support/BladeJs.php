<?php

namespace App\Support;

/**
 * Penyeragam pemakaian direktif @js di seluruh template Blade.
 *
 * Blade mengompilasi tag komponen lebih dulu, sebelum direksi lain. Karena
 * itu @js(...) yang ditulis di dalam atribut komponen tidak pernah
 * dikompilasi dan muncul apa adanya di HTML, sehingga tombol suara gagal
 * membacakan teks. To @js(...) diganti menjadi echo yang aman di kedua
 * tempat: atribut HTML biasa dan atribut tag komponen.
 */
class BladeJs
{
    /**
     * Ganti setiap @js(...) dengan echo yang menghasilkan string JSON.
     */
    public static function ganti(string $template): string
    {
        $hasil = '';
        $posisi = 0;

        while (($mulai = strpos($template, '@js(', $posisi)) !== false) {
            // "@js(" occupies four characters, so the expression starts at +4.
            $awal = $mulai + 4;
            $akhir = self::tutupKurung($template, $awal);

            if ($akhir === null) {
                $posisi = $awal;

                continue;
            }

            $ekspresi = substr($template, $awal, $akhir - $awal);

            $hasil .= substr($template, $posisi, $mulai - $posisi);
            $hasil .= '{{ \Illuminate\Support\Js::from('.$ekspresi.') }}';

            $posisi = $akhir + 1;
        }

        return $hasil.substr($template, $posisi);
    }

    /**
     * Cari kurung penutup yang sepadan, abaikan kurung di dalam string.
     *
     * Pemindaian dimulai setelah kurung pembuka "@js(", jadi level awal
     * sudah satu.
     */
    private static function tutupKurung(string $teks, int $mulai): ?int
    {
        $level = 1;
        $kutip = null;
        $panjang = strlen($teks);

        for ($i = $mulai; $i < $panjang; $i++) {
            $karakter = $teks[$i];

            if ($kutip !== null) {
                if ($karakter === '\\') {
                    $i++;
                } elseif ($karakter === $kutip) {
                    $kutip = null;
                }

                continue;
            }

            if ($karakter === '"' || $karakter === "'") {
                $kutip = $karakter;
            } elseif ($karakter === '(') {
                $level++;
            } elseif ($karakter === ')') {
                $level--;

                if ($level === 0) {
                    return $i;
                }
            }
        }

        return null;
    }
}
