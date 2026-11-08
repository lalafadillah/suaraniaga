<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Sumber tunggal daftar produk untuk halaman katalog dan API pencarian suara.
 *
 * Sumber utama adalah tabel products. Selama tabel itu masih kosong (misalnya
 * baru `php artisan migrate` tanpa seed), katalog memakai data contoh dari
 * config/suaranniaga.php supaya halaman tidak pernah tampil kosong.
 */
class Katalog
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function semua(): array
    {
        $daftar = array_values(config('suaranniaga.produk', []));

        $namaSudahAda = array_map(fn (array $item) => Str::lower((string) ($item['nama'] ?? '')), $daftar);

        foreach (Product::query()->with('seller')->orderBy('id')->get() as $produk) {
            if (in_array(Str::lower($produk->name), $namaSudahAda, true)) {
                continue;
            }

            $daftar[] = ProdukPresenter::ringkas($produk);
            $namaSudahAda[] = Str::lower($produk->name);
        }

        return $daftar;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function cari(string $q): array
    {
        $q = trim($q);

        if ($q === '') {
            return self::semua();
        }

        return array_values(array_filter(self::semua(), function (array $item) use ($q) {
            $teks = Str::lower(($item['nama'] ?? '').' '.($item['ringkas'] ?? '').' '.($item['deskripsi'] ?? ''));

            return Str::contains($teks, Str::lower($q));
        }));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function detail(int $id): ?array
    {
        foreach (self::semua() as $item) {
            if ((int) ($item['id'] ?? 0) === $id) {
                return $item;
            }
        }

        return null;
    }
}
