<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-text-scale="normal" data-contrast="standar" data-motion="normal">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SuaraNiaga' }} — SuaraNiaga</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <a href="#konten-utama" class="skip-link">Lompat ke konten utama</a>

    {{-- Live region: pengumuman status tanpa menggandakan suara pembaca layar --}}
    <div aria-live="polite" aria-atomic="true" class="sr-only" x-data x-text="$store.announcer.message"></div>

    <div class="flex min-h-screen flex-col">
        @include('partials.topnav')

        {{-- Judul ringkas untuk layar kecil, mengikuti frame mobile Figma --}}
        @php $mobileTitle = $mobileTitle ?? (($active ?? 'beranda') === 'beranda' ? 'SuaraNiaga' : ($title ?? 'SuaraNiaga')); @endphp
        <div class="px-[22px] pb-2 pt-5 lg:hidden">
            @if (($active ?? 'beranda') === 'beranda')
                <a href="{{ route('beranda') }}" class="text-[24px] font-bold text-brand">{{ $mobileTitle }}</a>
            @else
                <h1 class="text-[24px] font-bold text-ink">{{ $mobileTitle }}</h1>
            @endif
        </div>

        <div class="flex flex-1">
            @include('partials.sidebar')

            <main id="konten-utama" tabindex="-1" class="flex-1 px-[22px] pb-[92px] pt-6 lg:px-10 lg:py-10 lg:pb-10">
                <div class="mx-auto w-full max-w-[1080px]" x-data>
                    @hasSection('content')
                        @yield('content')
                    @endif
                </div>
            </main>
        </div>
    </div>

    @include('partials.mobile-nav')

    <footer class="border-t border-line bg-surface px-[22px] pb-[76px] pt-5 text-[13px] text-muted lg:hidden">
        <p class="flex items-center gap-2">
            <span>{{ $mode ?? 'Mode Pembeli' }}</span>
            <span aria-hidden="true">•</span>
            <a href="{{ route('pengaturan') }}" class="text-brand underline-offset-4 hover:underline">Bantuan</a>
        </p>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SuaraNiaga')</title>
    <link rel="stylesheet" href="{{ asset('css/suaraniaga.css') }}">
</head>
<body>
    <a class="skip-link" href="#konten-utama">Lompat ke konten utama</a>

    <header>
        <nav aria-label="Navigasi utama">
            <a href="{{ url('/') }}">SuaraNiaga</a>
            @auth
                @if (auth()->user()->isSeller())
                    <a href="{{ url('/seller/dashboard') }}">Dasbor penjual</a>
                @endif
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            @else
                <a href="{{ url('/login') }}">Masuk</a>
                <a href="{{ url('/register') }}">Daftar</a>
            @endauth
        </nav>
    </header>

    <main id="konten-utama">
        @yield('content')
    </main>

    <footer>
        <p>SuaraNiaga &mdash; marketplace ramah tunanetra.</p>
    </footer>
</body>
</html>
