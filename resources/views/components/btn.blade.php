@props([
    'variant' => 'primary', // primary | outline | soft | ghost
    'size' => 'md', // sm | md | lg
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-[10px] font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-60';

    $variants = [
        'primary' => 'bg-brand text-white hover:bg-brand/90',
        'outline' => 'border border-brand bg-surface text-brand hover:bg-brand-soft',
        'soft' => 'border border-line bg-surface text-ink hover:bg-brand-soft',
        'ghost' => 'text-brand hover:bg-brand-soft',
    ];

    $sizes = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-[54px] px-6 text-base',
        'lg' => 'h-16 px-6 text-base',
        'mobile' => 'h-[58px] px-4 text-[15px]',
        'mobile-lg' => 'h-[66px] px-4 text-[16px]',
    ];

    $classes = trim($base . ' ' . $variants[$variant] . ' ' . $sizes[$size]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
