<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Katalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $produk = Katalog::cari((string) $request->query('q', ''));

        $daftar = array_map(fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['nama'],
            'price' => (int) $item['harga'],
            'price_text' => 'Rp'.number_format((int) $item['harga'], 0, ',', '.'),
            'audio_description' => $item['audio'] ?? '',
            'url' => '/products/'.$item['id'],
        ], $produk);

        return response()->json([
            'count' => count($daftar),
            'products' => $daftar,
        ]);
    }
}
