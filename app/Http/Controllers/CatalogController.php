<?php

namespace App\Http\Controllers;

use App\Support\Katalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        return $this->halaman('beranda', [
            'title' => 'Beranda',
            'context' => 'Beranda pembeli • Fokus pada satu tindakan utama',
            'active' => 'beranda',
        ]);
    }

    public function search(Request $request): View
    {
        return $this->halaman('cari', [
            'title' => 'Cari Produk',
            'mobileTitle' => 'Cari produk',
            'context' => 'Pencarian produk • Input suara dan teks',
            'active' => 'cari',
            'produk' => Katalog::cari((string) $request->query('q', '')),
        ]);
    }

    public function show(int $produk): View
    {
        $data = Katalog::detail($produk);

        abort_if($data === null, 404, 'Produk tidak ditemukan.');

        return $this->halaman('detail-produk', [
            'title' => 'Detail Produk',
            'mobileTitle' => $data['nama'],
            'context' => 'Detail produk • Informasi utama dan deskripsi audio',
            'active' => 'cari',
            'produk' => $data,
        ]);
    }
}
