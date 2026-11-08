<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-text-scale="normal" data-contrast="standar" data-motion="normal">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SuaraNiaga' }} — SuaraNiaga</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $varian = $varian ?? 'a';
    $latar = $varian === 'a' ? 'bg-canvas-aks' : 'bg-canvas';
@endphp
<body class="min-h-screen {{ $latar }} font-sans antialiased">
    <a href="#konten-utama" class="skip-link">Lompat ke konten utama</a>

    {{-- Live region: pengumuman status tanpa menggandakan suara pembaca layar --}}
    <div aria-live="polite" aria-atomic="true" class="sr-only" x-data x-text="$store.announcer.message"></div>

    <div class="mx-auto flex min-h-screen w-full max-w-[1440px] flex-col px-[22px] py-[40px] sm:px-[48px] lg:px-[96px] lg:py-[70px]">
        @include('aksesibilitas.partials.kepala')

        <main id="konten-utama" tabindex="-1" class="flex-1 pt-[40px] lg:pt-[55px]">
            @yield('content')
        </main>

        @include('aksesibilitas.partials.kaki')
    </div>
</body>
</html>
