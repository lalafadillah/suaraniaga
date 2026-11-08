<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Katalog;
use App\Support\Rupiah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function new(Request $request): JsonResponse
    {
        $afterId = (int) $request->query('after_id', 0);

        $orders = Order::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $request->user()->id))
            ->with(['product', 'buyer'])
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(Katalog::BATAS)
            ->get();

        $daftar = $orders->map(fn (Order $order) => [
            'id' => $order->id,
            'product_name' => $order->product->name,
            'buyer_name' => $order->buyer->name,
            'total_price' => $order->total_price,
            'total_text' => Rupiah::format($order->total_price),
            'status' => $order->status,
        ])->all();

        return response()->json([
            'latest_id' => $daftar === [] ? $afterId : (int) end($daftar)['id'],
            'orders' => $daftar,
        ]);
    }
}
