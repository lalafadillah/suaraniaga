@php
    $context = $context ?? 'Beranda pembeli • Fokus pada satu tindakan utama';
    $mode = $mode ?? 'Mode Pembeli';
    $role = $role ?? 'pembeli';

    // Logo selalu menuju beranda sesuai peran, jadi tidak ada halaman tanpa
    // jalan pulang ke rumah.
    $rumah = match ($role) {
        'admin' => route('admin.dashboard'),
        'penjual' => route('penjual.dashboard'),
        default => route('beranda'),
    };
    $labelRumah = match ($role) {
        'admin' => 'SuaraNiaga, ringkasan admin',
        'penjual' => 'SuaraNiaga, ringkasan toko',
        default => 'SuaraNiaga, beranda',
    };
@endphp

<header class="sticky top-0 z-30 hidden h-[82px] items-center gap-6 border-b border-line bg-surface lg:flex lg:px-12">
    <a href="{{ $rumah }}" aria-label="{{ $labelRumah }}" class="shrink-0 text-[25px] font-bold text-brand">SuaraNiaga</a>

    <p class="flex-1 text-sm text-muted">{{ $context }}</p>

    <div class="ml-auto flex items-center gap-4 text-sm font-medium text-ink">
        <span>{{ $mode }}</span>
        <span aria-hidden="true" class="text-muted">•</span>
        <a href="{{ route('pengaturan') }}" class="rounded text-brand underline-offset-4 hover:underline">Bantuan</a>
        <form method="POST" action="{{ route('mode.aksesibilitas', 'aksesibilitas') }}">
            @csrf
            <button type="submit" class="rounded text-brand underline-offset-4 hover:underline">
                Mode aksesibilitas
            </button>
        </form>
    </div>
</header>
