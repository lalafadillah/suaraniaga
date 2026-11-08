@props([
    'kategori',
    'sekarang',
    'total',
])

{{-- Penanda langkah, contoh: "LOGIN  •  LANGKAH 1 / 2" --}}
<p {{ $attributes->merge(['class' => 'text-[18px] font-bold uppercase text-brand']) }}>
    {{ $kategori }} &nbsp;•&nbsp; Langkah {{ $sekarang }} / {{ $total }}
</p>
