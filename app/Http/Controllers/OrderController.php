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
    public function store(Request $request, Product $produk): RedirectResponse
    {
        abort_unless(
            $request->user()->role === User::ROLE_BUYER,
            403,
            'Hanya akun pembeli yang dapat memesan produk.'
        );

        // Nilai pesanan diambil dari server, bukan dari input pengguna.
        $order = new Order;
        $order->buyer_id = $request->user()->id;
        $order->product_id = $produk->id;
        $order->status = Order::STATUS_BARU;
        $order->total_price = $produk->price;
        $order->save();

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

        return $this->halaman('pesanan-berhasil', [
            'title' => 'Pesanan berhasil',
            'mobileTitle' => 'Pesanan berhasil',
            'active' => 'pesanan',
            'context' => 'Pesanan berhasil dibuat',
            'order' => $order,
        ]);
    }

    public function sukses(): View
    {
        return $this->halaman('pesanan-berhasil', [
            'title' => 'Pesanan berhasil',
            'mobileTitle' => 'Pesanan berhasil',
            'active' => 'pesanan',
            'context' => 'Pesanan berhasil dibuat',
        ]);
    }
}
