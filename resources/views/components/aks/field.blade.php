@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'placeholder' => null,
    'required' => false,
    'autocomplete' => null,
    'inputmode' => null,
])

{{--
    Satu kolom isian besar untuk Mode Aksesibilitas. Pada tiap langkah hanya
    satu kolom yang tampil, jadi pembaca layar tidak perlu melewati banyak
    isian sekaligus.
--}}

@php
    $id = $attributes->get('id', 'aks-' . $name);
    $error = $errors->first($name);
@endphp

<div class="w-full">
    <label for="{{ $id }}" class="block text-[28px] font-bold leading-tight text-ink">
        {{ $label }}
        @if ($required)
            <span aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
        @endif
    </label>

    <input id="{{ $id }}"
           name="{{ $name }}"
           type="{{ $type }}"
           value="{{ old($name, $value) }}"
           @if ($placeholder) placeholder="{{ $placeholder }}" @endif
           @if ($required) required @endif
           @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if ($inputmode) inputmode="{{ $inputmode }}" @endif
           @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-galat" @endif
           {{ $attributes->except('id')->merge(['class' => 'mt-4 h-[100px] w-full rounded-[12px] border-[3px] border-ink bg-white px-[34px] text-[25px] text-body placeholder:text-muted focus:border-brand']) }}>

    @if ($error)
        <p id="{{ $id }}-galat" class="mt-3 text-[20px] font-medium text-danger">{{ $error }}</p>
    @endif
</div>
