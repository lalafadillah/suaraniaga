@php
    $context = $context ?? 'Beranda pembeli • Fokus pada satu tindakan utama';
    $mode = $mode ?? 'Mode Pembeli';
@endphp

<header class="sticky top-0 z-30 hidden h-[82px] items-center gap-6 border-b border-line bg-surface lg:flex lg:px-12">
    <a href="{{ route('beranda') }}" class="shrink-0 text-[25px] font-bold text-brand">SuaraNiaga</a>

    <p class="flex-1 text-sm text-muted">{{ $context }}</p>

    <div class="ml-auto flex items-center gap-4 text-sm font-medium text-ink">
        <span>{{ $mode }}</span>
        <span aria-hidden="true" class="text-muted">•</span>
        <a href="{{ route('pengaturan') }}" class="rounded text-brand underline-offset-4 hover:underline">Bantuan</a>

        @auth
            <span aria-hidden="true" class="text-muted">•</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded text-brand underline-offset-4 hover:underline">Keluar</button>
            </form>
        @else
            <span aria-hidden="true" class="text-muted">•</span>
            <a href="{{ route('login') }}" class="rounded text-brand underline-offset-4 hover:underline">Masuk</a>
        @endauth
    </div>
</header>
