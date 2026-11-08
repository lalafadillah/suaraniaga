<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = Product::query()
            ->when($q !== '', function ($inner) use ($q) {
                $inner->where(function ($where) use ($q) {
                    $where->where('name', 'like', '%'.$q.'%')
                        ->orWhere('description', 'like', '%'.$q.'%');
                });
            });

        $count = (clone $query)->count();

        $products = $query
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'price_text' => $product->price_text,
                'audio_description' => $product->audio_description,
                'url' => '/products/'.$product->id,
            ]);

        return response()->json([
            'count' => $count,
            'products' => $products,
        ]);
    }
}
