<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HalamanController extends Controller
{
    public function beranda(): View
    {
        return $this->halaman('beranda', [
            'title' => 'Beranda',
            'context' => 'Beranda pembeli • Fokus pada satu tindakan utama',
            'active' => 'beranda',
        ]);
    }

    public function keranjang(): View
    {
        return $this->halaman('keranjang', [
            'title' => 'Keranjang',
            'context' => 'Keranjang • Ringkasan barang sebelum checkout',
            'active' => 'keranjang',
        ]);
    }

    public function checkout(): View
    {
        return $this->halaman('checkout', [
            'title' => 'Checkout',
            'context' => 'Checkout • Formulir sederhana dan terstruktur',
            'active' => 'keranjang',
        ]);
    }

    public function pesanan(): View
    {
        return $this->halaman('pesanan', [
            'title' => 'Pesanan Saya',
            'context' => 'Pesanan saya • Riwayat dan status pesanan',
            'active' => 'pesanan',
            'pesanan' => config('suaranniaga.pesanan', []),
        ]);
    }

    public function pesananBerhasil(): View
    {
        return $this->halaman('pesanan-berhasil', [
            'title' => 'Pesanan Berhasil',
            'context' => 'Konfirmasi pesanan • Feedback jelas setelah aksi',
            'active' => 'pesanan',
        ]);
    }

    public function favorit(): View
    {
        return $this->halaman('favorit', [
            'title' => 'Favorit',
            'context' => 'Favorit • Produk yang kamu simpan',
            'active' => 'favorit',
        ]);
    }

    public function profil(): View
    {
        return $this->halaman('profil', [
            'title' => 'Profil',
            'context' => 'Profil • Data akun dan preferensi',
            'active' => 'profil',
        ]);
    }

    public function pengaturan(): View
    {
        return $this->halaman('pengaturan', [
            'title' => 'Pengaturan Aksesibilitas',
            'context' => 'Pengaturan aksesibilitas • Preferensi pengguna',
            'active' => 'pengaturan',
        ]);
    }
}
