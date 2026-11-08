{{--
    Kepala halaman Mode Aksesibilitas: merek dan penanda mode. Tanpa navigasi
    kecil supaya pengguna hanya punya satu jalur: isi konten, lalu tombol
    besar di bawahnya.

    Varian A memakai mark oranye, varian B memakai mark biru.
--}}
@php
    $varian = $varian ?? 'a';
    $mark = $varian === 'a' ? 'bg-accent' : 'bg-brand';
    $namaMerek = $varian === 'a' ? 'text-brand font-bold' : 'text-ink font-semibold';
    $badge = $varian === 'a' ? 'text-brand font-bold' : 'text-brand font-semibold';

    // Merek di kepala halaman juga jadi jalan pulang ke beranda sesuai peran,
    // supaya pengguna tidak perlu menggulir ke bawah untuk pulang ke rumah.
    $pengguna = auth()->user();
    $rumah = $pengguna?->isSeller() ? route('penjual.dashboard') : route('beranda');
    $labelRumah = $pengguna?->isSeller() ? 'SuaraNiaga, ringkasan toko' : 'SuaraNiaga, beranda';
@endphp

<header class="flex items-start justify-between gap-6">
    <div class="flex items-center gap-5">
        <span aria-hidden="true" class="block h-[44px] w-[12px] shrink-0 rounded-[4px] {{ $mark }}"></span>
        <a href="{{ $rumah }}" aria-label="{{ $labelRumah }}" class="text-[30px] leading-none {{ $namaMerek }} underline-offset-4 hover:underline">
            SuaraNiaga
        </a>
    </div>

    <p class="shrink-0 text-right text-[16px] {{ $badge }}">MODE AKSESIBILITAS</p>
</header>
