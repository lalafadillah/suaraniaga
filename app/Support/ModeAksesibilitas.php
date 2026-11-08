<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Mode Aksesibilitas.
 *
 * Mode ini adalah tampilan kedua yang dipakai pengguna tunanetra: satu
 * tindakan per layar, tombol besar, dan navigasi linear. Pilihan mode disimpan
 * di sesi supaya bisa dipakai tamu, lalu disalin ke kolom akun supaya tetap
 * berlaku setelah pengguna keluar dan masuk lagi.
 */
class ModeAksesibilitas
{
    public const KUNCI_SESI = 'mode_aksesibilitas';

    public static function aktif(?Request $request = null): bool
    {
        $request = $request ?? request();

        $user = $request->user();

        // Halaman admin memakai tampilan admin sendiri, bukan Mode Aksesibilitas.
        if ($user !== null && $user->isAdmin()) {
            return false;
        }

        if ($request->hasSession()) {
            $sesi = $request->session()->get(self::KUNCI_SESI);

            if ($sesi !== null) {
                return (bool) $sesi;
            }
        }

        return (bool) ($user?->mode_aksesibilitas ?? false);
    }

    public static function simpan(Request $request, bool $aktif): void
    {
        if ($request->hasSession()) {
            $request->session()->put(self::KUNCI_SESI, $aktif);
        }

        $user = $request->user();

        if ($user !== null) {
            $user->forceFill(['mode_aksesibilitas' => $aktif])->save();
        }
    }

    /**
     * Pilih nama tampilan sesuai mode yang sedang aktif.
     */
    public static function layar(string $normal, string $aksesibilitas): string
    {
        return self::aktif() ? $aksesibilitas : $normal;
    }
}
