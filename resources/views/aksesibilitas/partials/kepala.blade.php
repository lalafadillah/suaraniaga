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
@endphp

<header class="flex items-start justify-between gap-6">
    <div class="flex items-center gap-5">
        <span aria-hidden="true" class="block h-[44px] w-[12px] shrink-0 rounded-[4px] {{ $mark }}"></span>
        <span class="text-[30px] leading-none {{ $namaMerek }}">SuaraNiaga</span>
    </div>

    <p class="shrink-0 text-right text-[16px] {{ $badge }}">MODE AKSESIBILITAS</p>
</header>
