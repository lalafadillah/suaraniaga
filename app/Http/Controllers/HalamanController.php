<?php

namespace App\Http\Controllers;

use App\Support\Katalog;
use App\Support\ModeAksesibilitas;
use Illuminate\Http\Request;
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

    public function favorit(): View
    {
        return $this->halaman('favorit', [
            'title' => 'Favorit',
            'context' => 'Favorit • Produk yang kamu simpan',
            'active' => 'favorit',
            'produk' => Katalog::semua(3),
        ]);
    }

    public function pengaturan(Request $request): View
    {
        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.pengaturan', [
                'title' => 'Bantuan aksesibilitas',
            ]);
        }

        return $this->halaman('pengaturan', [
            'title' => 'Pengaturan Aksesibilitas',
            'context' => 'Pengaturan aksesibilitas • Preferensi pengguna',
            'active' => 'pengaturan',
        ]);
    }
}
