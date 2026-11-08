<!DOCTYPE html>
<html lang="id" data-text-scale="normal" data-contrast="standar" data-motion="normal">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SuaraNiaga' }} — SuaraNiaga</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Aset auth hanya ikut termuat kalau sudah ada di build atau dev server.
         Tanpa ini halaman masuk akan 500 saat `npm run build` belum dijalankan. --}}
    @php
        $manifestAuth = public_path('build/manifest.json');
        $isiManifest = file_exists($manifestAuth) ? (string) file_get_contents($manifestAuth) : '';
        $asetAuthAda = file_exists(public_path('hot'))
            || (str_contains($isiManifest, 'resources/css/auth.css')
                && str_contains($isiManifest, 'resources/js/auth.js'));
    @endphp

    @if ($asetAuthAda)
        @vite(['resources/css/auth.css', 'resources/js/auth.js'])
    @endif
</head>
<body class="min-h-screen font-sans antialiased">
    <a href="#konten-utama" class="skip-link">Lompat ke konten utama</a>

    {{-- Live region: pengumuman status tanpa menggandakan suara pembaca layar --}}
    <div aria-live="polite" aria-atomic="true" class="sr-only" x-data x-text="$store.announcer.message"></div>

    <div class="flex min-h-screen flex-col bg-canvas">
        <header class="border-b border-line bg-surface px-[22px] py-4 lg:px-10">
            <a href="{{ route('beranda') }}" class="text-[22px] font-bold text-brand">SuaraNiaga</a>
        </header>

        <main id="konten-utama" tabindex="-1" class="flex-1 px-[22px] py-8 lg:py-14">
            <div class="mx-auto w-full max-w-[520px]">
                @yield('content')
            </div>
        </main>

        <footer class="border-t border-line bg-surface px-[22px] py-5 text-[13px] text-muted">
            <p>SuaraNiaga — marketplace ramah tunanetra.</p>

            <form method="POST" action="{{ route('mode.aksesibilitas', 'aksesibilitas') }}" class="mt-3">
                @csrf
                <button type="submit" class="text-brand underline underline-offset-4">
                    Mode aksesibilitas
                </button>
            </form>
        </footer>
    </div>
</body>
</html>
