<?php

namespace App\Http\Controllers;

use App\Support\ModeAksesibilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModeController extends Controller
{
    /**
     * Mengganti antara mode normal dan Mode Aksesibilitas.
     *
     * Pilihan disimpan di sesi untuk tamu dan disalin ke akun supaya tetap
     * berlaku setelah pengguna keluar dan masuk lagi.
     */
    public function update(Request $request, string $mode): RedirectResponse
    {
        $aktif = $mode === 'aksesibilitas';

        ModeAksesibilitas::simpan($request, $aktif);

        return back()->with('status', $aktif
            ? 'Mode aksesibilitas aktif. Tampilan memakai satu tindakan per layar.'
            : 'Mode normal aktif.');
    }
}
