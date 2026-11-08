@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-7']) }}>
    {{-- Di layar kecil judul halaman sudah ada di header, jadi h1 desktop disembunyikan --}}
    <h1 class="hidden text-[30px] font-bold leading-tight text-ink lg:block">{{ $title }}</h1>

    @if ($description)
        <p class="mt-2 max-w-[760px] text-base text-muted">{{ $description }}</p>
    @endif
</div>
