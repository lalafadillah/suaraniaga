<!DOCTYPE html>
<html lang="id" data-text-scale="normal" data-contrast="standar" data-motion="normal" data-mode-aksesibilitas="{{ \App\Support\ModeAksesibilitas::preferensi() ? '1' : '0' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses ditolak — SuaraNiaga</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #fdfcfb; color: #1f2430; line-height: 1.6; }
        .skip-link { position: absolute; left: -9999px; }
        .skip-link:focus { left: 12px; top: 12px; background: #ffffff; padding: 10px 16px; border: 2px solid #1f2430; z-index: 10; }
        main { max-width: 640px; margin: 0 auto; padding: 48px 22px; }
        h1 { font-size: 30px; margin: 0 0 16px; }
        .pesan { border: 2px solid #1f2430; border-radius: 10px; padding: 16px; background: #ffffff; }
        .tautan { display: inline-block; margin-top: 24px; background: #0f5c4e; color: #ffffff; padding: 14px 24px; border-radius: 10px; font-weight: 600; text-decoration: none; }
        .tautan:focus, .tautan:hover { background: #0b463c; }
    </style>

    {{-- Navigasi suara global juga berlaku di halaman galat. Aset app.js
         hanya ikut termuat kalau sudah ada di build atau dev server. --}}
    @php
        $manifestApp = public_path('build/manifest.json');
        $isiManifestApp = file_exists($manifestApp) ? (string) file_get_contents($manifestApp) : '';
        $asetAppAda = file_exists(public_path('hot'))
            || str_contains($isiManifestApp, 'resources/js/app.js');
    @endphp

    @if ($asetAppAda)
        @vite(['resources/js/app.js'])
    @endif
</head>
<body>
    <a class="skip-link" href="#konten-utama">Lompat ke konten utama</a>

    <main id="konten-utama" tabindex="-1">
        <h1>Akses ditolak</h1>

        <p class="pesan" role="alert">{{ $exception->getMessage() }}</p>

        @php
            $peran = auth()->user()?->role;
            $tujuan = match ($peran) {
                'seller' => route('seller.dashboard'),
                'admin' => route('admin.dashboard'),
                default => route('beranda'),
            };
        @endphp

        <a class="tautan" href="{{ $tujuan }}">Kembali ke halaman utama saya</a>
    </main>
</body>
</html>
