@props([
    'tone' => 'neutral', // neutral | success | info | warning
])

@php
    $tones = [
        'neutral' => 'bg-brand-soft text-brand',
        'success' => 'bg-success-soft text-success',
        'info' => 'bg-brand-soft text-brand',
        'warning' => 'bg-amber-soft text-ink',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ' . $tones[$tone]]) }}>
    {{ $slot }}
</span>
