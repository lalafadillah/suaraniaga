<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\ProdukPresenter;
use App\Support\Rupiah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerController extends Controller
{
    /**
     * Label status pesanan untuk ditampilkan ke pengguna.
     */
    private const LABEL_STATUS = [
        Order::STATUS_BARU => 'Pesanan baru',
        Order::STATUS_DIPROSES => 'Sedang diproses',
        Order::STATUS_DIKIRIM => 'Dalam pengiriman',
        Order::STATUS_SELESAI => 'Selesai',
    ];

    public function dashboard(): View
    {
        return $this->halaman('penjual.dashboard', [
            'title' => 'Dashboard Penjual',
            'mobileTitle' => 'Ringkasan toko',
            'context' => 'Dashboard penjual • Ringkasan aktivitas toko',
            'active' => 'ringkasan',
        ]);
    }

    public function produk(Request $request): View
    {
        $daftar = ProdukPresenter::daftar(
            $request->user()->products()->with('seller')->orderByDesc('id')->get()
        );

        // View ini masih membaca daftar produk dari config, jadi isinya
        // ditimpa sementara dengan produk milik penjual yang sedang masuk.
        config(['suaranniaga.produk' => $daftar]);

        return $this->halaman('penjual.produk', [
            'title' => 'Produk Saya',
            'mobileTitle' => 'Produk saya',
            'context' => 'Produk saya • Daftar produk aktif',
            'active' => 'produk',
            'produk' => $daftar,
            'products' => $daftar,
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

    public function pesanan(Request $request): View
    {
        $orders = Order::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $request->user()->id))
            ->with(['product', 'buyer'])
            ->orderByDesc('id')
            ->get();

        $daftar = $orders->map(fn (Order $order) => [
            'id' => 'SN-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'produk' => $order->product->name,
            'status' => self::LABEL_STATUS[$order->status] ?? $order->status,
            'helper' => $order->buyer->name.' • '.Rupiah::format($order->total_price),
            'aksi' => 'Lihat detail',
        ])->all();

        // View ini masih membaca daftar pesanan dari config, jadi isinya
        // ditimpa sementara dengan pesanan untuk produk milik penjual ini.
        config(['suaranniaga.pesanan' => $daftar]);

        return $this->halaman('penjual.pesanan', [
            'title' => 'Pesanan Masuk',
            'mobileTitle' => 'Pesanan masuk',
            'context' => 'Pesanan masuk • Antrean pesanan toko',
            'active' => 'pesanan-masuk',
            'pesanan' => $daftar,
            'orders' => $daftar,
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
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'audio_description' => ['required', 'string', 'min:10'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 120 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka rupiah.',
            'price.min' => 'Harga harus lebih besar dari nol.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'audio_description.required' => 'Deskripsi audio wajib diisi.',
            'audio_description.min' => 'Deskripsi audio minimal 10 karakter.',
            'image_url.string' => 'Tautan gambar harus berupa teks.',
        ]);

        $request->user()->products()->create([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'] ?? '',
            'audio_description' => $data['audio_description'],
            'image_url' => $data['image_url'] ?? null,
        ]);

        return redirect()->route('seller.dashboard')
            ->with('status', 'Produk berhasil disimpan');
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

        if (! $order->bisaMajuKe($data['status'])) {
            return back()->withErrors([
                'status' => 'Status pesanan tidak bisa dikembalikan ke langkah sebelumnya.',
            ]);
        }

        $order->update(['status' => $data['status']]);

        return back()->with('status', 'Status pesanan diperbarui menjadi '.$data['status'].'.');
    }
}
