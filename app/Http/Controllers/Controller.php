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
        $user = auth()->user();

        $peran = match (true) {
            (bool) $user?->isAdmin() => 'admin',
            (bool) $user?->isSeller() => 'penjual',
            default => 'pembeli',
        };

        $mode = match ($peran) {
            'admin' => 'Mode Admin',
            'penjual' => 'Mode Penjual',
            default => 'Mode Pembeli',
        };

        $context = match ($peran) {
            'admin' => 'Ringkasan admin • Pantau akun, produk, dan pesanan',
            'penjual' => 'Ringkasan toko • Pantau pesanan masuk',
            default => 'Beranda pembeli • Fokus pada satu tindakan utama',
        };

        return view($view, array_merge([
            'role' => $peran,
            'mode' => $mode,
            'context' => $context,
            'title' => 'SuaraNiaga',
            'mobileTitle' => 'SuaraNiaga',
            'active' => 'beranda',
        ], $data));
    }
}
