<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Katalog;
use App\Support\Rupiah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $daftar = array_map(fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['nama'],
            'price' => (int) $item['harga'],
            'price_text' => Rupiah::format($item['harga']),
            'audio_description' => (string) ($item['audio'] ?? ''),
            'url' => '/products/'.$item['id'],
        ], Katalog::cari($q));

        return response()->json([
            'count' => count($daftar),
            'products' => $daftar,
        ]);
    }
}
