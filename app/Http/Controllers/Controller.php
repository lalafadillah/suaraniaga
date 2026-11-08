<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

abstract class Controller
{
    /**
     * Membangun halaman dengan data navigasi yang dipakai layout dan partial.
     *
     * @param  array<string, mixed>  $data
     */
    protected function halaman(string $view, array $data = []): View
    {
        $penjual = auth()->user()?->isSeller() ?? false;

        return view($view, array_merge([
            'role' => $penjual ? 'penjual' : 'pembeli',
            'mode' => $penjual ? 'Mode Penjual' : 'Mode Pembeli',
            'title' => 'SuaraNiaga',
            'mobileTitle' => 'SuaraNiaga',
            'active' => 'beranda',
            'context' => $penjual
                ? 'Ringkasan toko • Pantau pesanan masuk'
                : 'Beranda pembeli • Fokus pada satu tindakan utama',
        ], $data));
    }
}
