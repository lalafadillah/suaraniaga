<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\ModeAksesibilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Membeli satu produk langsung dari halaman detail.
     */
    public function store(Request $request, Product $produk): RedirectResponse
    {
        abort_unless(
            $request->user()->role === User::ROLE_BUYER,
            403,
            'Hanya akun pembeli yang dapat memesan produk.'
        );

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ], [
            'quantity.integer' => 'Jumlah harus berupa angka.',
            'quantity.min' => 'Jumlah minimal satu barang.',
            'quantity.max' => 'Jumlah maksimal 20 barang.',
        ]);

        $jumlah = (int) ($data['quantity'] ?? 1);

        // Nilai pesanan diambil dari server, bukan dari input pengguna.
        $order = DB::transaction(function () use ($request, $produk, $jumlah) {
            $item = Product::query()->lockForUpdate()->findOrFail($produk->id);

            if ($item->stock < $jumlah) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok '.$item->name.' tinggal '.$item->stock.' barang.',
                ]);
            }

            $item->decrement('stock', $jumlah);

            $order = new Order;
            $order->buyer_id = $request->user()->id;
            $order->product_id = $item->id;
            $order->quantity = $jumlah;
            $order->status = Order::STATUS_BARU;
            $order->total_price = $item->price * $jumlah;
            $order->save();

            return $order;
        });

        return redirect()->route('orders.confirmation', $order);
    }

    /**
     * Riwayat pesanan pembeli.
     */
    public function pesanan(Request $request): View
    {
        $pesanan = $request->user()
            ->orders()
            ->with('product')
            ->orderByDesc('id')
            ->get();

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.pesanan', [
                'title' => 'Pesanan Saya',
                'pesanan' => $pesanan,
                'halaman' => max(1, (int) $request->query('halaman', 1)),
            ]);
        }

        return $this->halaman('pesanan', [
            'title' => 'Pesanan Saya',
            'context' => 'Pesanan saya • Riwayat dan status pesanan',
            'active' => 'pesanan',
            'pesanan' => $pesanan,
            'orders' => $pesanan,
        ]);
    }

    /**
     * Pembeli mengonfirmasi barang sudah diterima.
     *
     * Status hanya boleh maju, jadi pesanan yang belum dikirim tidak bisa
     * langsung diselesaikan oleh pembeli.
     */
    public function terima(Request $request, Order $order): RedirectResponse
    {
        abort_unless(
            $order->buyer_id === $request->user()->id,
            403,
            'Pesanan ini bukan milik Anda.'
        );

        if (! $order->bisaMajuKe(Order::STATUS_SELESAI)) {
            return back()->withErrors([
                'status' => 'Pesanan baru bisa diselesaikan setelah dikirim.',
            ]);
        }

        $order->update(['status' => Order::STATUS_SELESAI]);

        return back()->with('status', 'Terima kasih. Pesanan ditandai selesai.');
    }

    public function confirmation(Request $request, Order $order): View
    {
        abort_unless(
            $order->buyer_id === $request->user()->id,
            403,
            'Pesanan ini bukan milik Anda.'
        );

        $order->load('product');

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.pesanan', [
                'title' => 'Pesanan berhasil',
                'pesanan' => collect([$order]),
                'halaman' => 1,
            ]);
        }

        return $this->halaman('pesanan-berhasil', [
            'title' => 'Pesanan berhasil',
            'mobileTitle' => 'Pesanan berhasil',
            'active' => 'pesanan',
            'context' => 'Pesanan berhasil dibuat',
            'order' => $order,
            'orders' => collect([$order]),
        ]);
    }

    public function sukses(Request $request): View
    {
        $ids = (array) $request->session()->get('pesanan_baru', []);
        $request->session()->forget('pesanan_baru');

        $orders = Order::query()
            ->where('buyer_id', $request->user()->id)
            ->whereIn('id', $ids)
            ->with('product')
            ->orderBy('id')
            ->get();

        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.pesanan', [
                'title' => 'Pesanan berhasil',
                'pesanan' => $orders,
                'halaman' => 1,
            ]);
        }

        return $this->halaman('pesanan-berhasil', [
            'title' => 'Pesanan berhasil',
            'mobileTitle' => 'Pesanan berhasil',
            'active' => 'pesanan',
            'context' => 'Pesanan berhasil dibuat',
            'order' => $orders->first(),
            'orders' => $orders,
        ]);
    }
}
