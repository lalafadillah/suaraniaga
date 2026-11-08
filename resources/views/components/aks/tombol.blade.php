@props([
    'varian' => 'a', // a | b
    'warna' => 'primer', // primer | sekunder | sukses | redup
    'href' => null,
    'type' => 'submit',
    'name' => null,
    'value' => null,
])

{{--
    Tombol Mode Aksesibilitas.

    Varian A memakai tombol tinggi berbingkai oranye untuk tindakan utama.
    Varian B memakai tombol biru ringkas, tombol hijau untuk tindakan yang
    mengubah data, dan tombol putih untuk navigasi.
--}}

@php
    if ($varian === 'a') {
        $kelas = match ($warna) {
            'biru' => 'border-2 border-brand bg-brand text-white',
            'sekunder' => 'border-2 border-brand bg-white text-brand',
            'sukses' => 'border-0 bg-success text-white',
            'redup' => 'border-2 border-line bg-white text-muted',
            default => 'border-[5px] border-accent bg-brand text-white',
        };

        $kelas .= ' min-h-[82px] rounded-[14px] px-6 py-5 text-[25px] font-semibold leading-snug';
    } else {
        $kelas = match ($warna) {
            'sekunder' => 'border border-line-strong bg-white text-brand',
            'sukses' => 'border-0 bg-success text-white',
            'redup' => 'border border-line bg-white text-muted',
            default => 'border-0 bg-brand text-white',
        };

        $kelas .= ' min-h-[68px] rounded-[12px] px-6 py-4 text-[23px] font-semibold leading-snug';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}"
            @if ($name) name="{{ $name }}" value="{{ $value }}" @endif
            {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</button>
@endif
