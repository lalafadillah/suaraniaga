@props([
    'tipe' => 'galat', // galat | status
    'judul' => null,
])

{{--
    Ringkasan pesan. Galat memakai role="alert" dan tabindex="-1" supaya
    fokus pembaca layar langsung pindah ke sini, sesuai aturan aksesibilitas.
--}}

@php
    $adaGalat = $tipe === 'galat';
    $kelas = $adaGalat
        ? 'rounded-[14px] border-[3px] border-danger bg-danger-soft px-6 py-5'
        : 'rounded-[14px] border-[3px] border-brand bg-white px-6 py-5';
@endphp

@if ($adaGalat && $errors->any())
    <div id="ringkasan-galat" role="alert" tabindex="-1" {{ $attributes->merge(['class' => $kelas]) }}>
        <h2 class="text-[24px] font-bold text-ink">{{ $judul ?? 'Ada kesalahan' }}</h2>
        <ul class="mt-2 list-disc pl-8 text-[20px] text-body">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@elseif (! $adaGalat && session('status'))
    <div role="status" {{ $attributes->merge(['class' => $kelas]) }}>
        <p class="text-[20px] font-semibold text-ink">{{ session('status') }}</p>
    </div>
@endif
