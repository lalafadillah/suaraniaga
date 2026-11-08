<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sumber tunggal daftar produk untuk halaman katalog dan API pencarian suara.
 *
 * Sumber data adalah tabel products. Pencarian memakai LIKE pada kolom name
 * dan description, sama seperti yang dipakai API untuk fitur suara.
 */
class Katalog
{
    public const BATAS = 50;

    /**
     * @return list<array<string, mixed>>
     */
    public static function semua(int $batas = self::BATAS): array
    {
        return ProdukPresenter::daftar(self::query($batas)->get());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function cari(string $q, int $batas = self::BATAS): array
    {
        $q = trim($q);

        $query = self::query($batas);

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $pola = '%'.self::escape($q).'%';

                $builder->where('name', 'like', $pola)
                    ->orWhere('description', 'like', $pola);
            });
        }

        return ProdukPresenter::daftar($query->get());
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function detail(int $id): ?array
    {
        $produk = Product::query()->with('seller')->find($id);

        return $produk === null ? null : ProdukPresenter::ringkas($produk);
    }

    /**
     * @return Builder<Product>
     */
    private static function query(int $batas)
    {
        return Product::query()
            ->with('seller')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($batas);
    }

    /**
     * Karakter wildcard LIKE harus di-escape agar pencarian tidak berubah arti.
     */
    private static function escape(string $q): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
    }
}
