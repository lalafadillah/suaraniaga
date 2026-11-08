@props([
    'kembali' => null,
    'labelKembali' => 'Kembali',
    'labelLanjut' => 'Lanjutkan',
])

{{--
    Baris tombol Kembali di kiri dan Lanjutkan di kanan, sesuai desain Mode
    Aksesibilitas. Tombol Lanjut selalu menjadi fokus pertama saat halaman
    langkah terbuka.
--}}
<div {{ $attributes->merge(['class' => 'mt-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    @if ($kembali)
        <x-aks.tombol warna="sekunder" href="{{ $kembali }}" class="sm:w-[300px]">{{ $labelKembali }}</x-aks.tombol>
    @else
        <span class="hidden sm:block sm:w-[300px]"></span>
    @endif

    <x-aks.tombol type="submit" class="sm:w-[300px]">{{ $labelLanjut }}</x-aks.tombol>
</div>
