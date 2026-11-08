<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function new(Request $request): JsonResponse
    {
        $afterId = (int) $request->query('after_id', 0);

        $sellerId = $request->user()->id;

        $latestId = Order::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $sellerId))
            ->max('id') ?? 0;

        $orders = Order::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $sellerId))
            ->with(['product', 'buyer'])
            ->where('id', '>', $afterId)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'product_name' => $order->product->name,
                'buyer_name' => $order->buyer->name,
                'total_price' => $order->total_price,
                'total_text' => $order->total_text,
                'status' => $order->status,
            ]);

        return response()->json([
            'latest_id' => (int) $latestId,
            'orders' => $orders,
        ]);
    }
}
