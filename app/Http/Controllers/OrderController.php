<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless(
            $request->user()->role === User::ROLE_BUYER,
            403,
            'Hanya akun pembeli yang dapat memesan produk.'
        );

        $order = Order::create([
            'buyer_id' => $request->user()->id,
            'product_id' => $product->id,
            'status' => Order::STATUS_BARU,
            'total_price' => $product->price,
        ]);

        return redirect()->route('orders.confirmation', $order);
    }

    public function confirmation(Request $request, Order $order): View
    {
        abort_unless(
            $order->buyer_id === $request->user()->id,
            403,
            'Pesanan ini bukan milik Anda.'
        );

        $order->load('product');

        return view('buyer.orders.confirmation', compact('order'));
    }
}
