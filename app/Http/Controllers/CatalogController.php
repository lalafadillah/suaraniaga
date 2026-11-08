<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with('seller')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('description', 'like', '%'.$q.'%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('buyer.index', compact('products', 'q'));
    }

    public function show(Product $product): View
    {
        $product->load('seller');

        return view('buyer.products.show', compact('product'));
    }
}
