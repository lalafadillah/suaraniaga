<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

class ProdukPresenter
{
    /**
     * Bentuk data produk yang dipakai seluruh view frontend.
     *
     * @return array<string, mixed>
     */
    public static function ringkas(Product $produk): array
    {
        return [
            'id' => $produk->id,
            'nama' => $produk->name,
            'ringkas' => Str::limit($produk->description, 70),
            'harga' => $produk->price,
            'stok' => $produk->stock,
            'deskripsi' => $produk->description,
            'toko' => $produk->seller?->name ?? 'Penjual SuaraNiaga',
            'rating' => '-',
            'kota' => '-',
            'audio' => $produk->audio_description,
        ];
    }

    /**
     * @param  iterable<Product>  $produk
     * @return list<array<string, mixed>>
     */
    public static function daftar(iterable $produk): array
    {
        $daftar = [];

        foreach ($produk as $item) {
            $daftar[] = self::ringkas($item);
        }

        return $daftar;
    }
}
