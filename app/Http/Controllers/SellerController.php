<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerController extends Controller
{
    public function dashboard(): View
    {
        return $this->halaman('penjual.dashboard', [
            'title' => 'Dashboard Penjual',
            'mobileTitle' => 'Ringkasan toko',
            'context' => 'Dashboard penjual • Ringkasan aktivitas toko',
            'active' => 'ringkasan',
        ]);
    }

    public function produk(): View
    {
        return $this->halaman('penjual.produk', [
            'title' => 'Produk Saya',
            'mobileTitle' => 'Produk saya',
            'context' => 'Produk saya • Daftar produk aktif',
            'active' => 'produk',
        ]);
    }

    public function createProduct(): View
    {
        return $this->halaman('penjual.tambah-produk', [
            'title' => 'Tambah Produk',
            'mobileTitle' => 'Tambah produk',
            'context' => 'Tambah produk • Alur input suara bertahap',
            'active' => 'tambah',
        ]);
    }

    public function pesanan(): View
    {
        return $this->halaman('penjual.pesanan', [
            'title' => 'Pesanan Masuk',
            'mobileTitle' => 'Pesanan masuk',
            'context' => 'Pesanan masuk • Antrean pesanan toko',
            'active' => 'pesanan-masuk',
        ]);
    }

    public function toko(): View
    {
        return $this->halaman('penjual.toko', [
            'title' => 'Toko Saya',
            'mobileTitle' => 'Toko saya',
            'context' => 'Toko saya • Profil toko dan pengiriman',
            'active' => 'toko',
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string'],
            'audio_description' => ['required', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka rupiah.',
            'price.min' => 'Harga tidak boleh negatif.',
            'description.required' => 'Deskripsi wajib diisi.',
            'audio_description.required' => 'Deskripsi audio wajib diisi.',
        ]);

        $request->user()->products()->create($data);

        return redirect()->route('penjual.produk')
            ->with('status', 'Produk berhasil disimpan.');
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ], [
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status pesanan tidak dikenal.',
        ]);

        abort_unless(
            $order->product()->where('seller_id', $request->user()->id)->exists(),
            403,
            'Pesanan ini bukan untuk produk Anda.'
        );

        $order->update(['status' => $data['status']]);

        return back()->with('status', 'Status pesanan diperbarui menjadi '.$data['status'].'.');
    }
}
