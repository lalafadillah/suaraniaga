@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'autocomplete' => null,
    'inputmode' => null,
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $described = collect([$hint ? $id . '-hint' : null, $error ? $id . '-galat' : null])
        ->filter()
        ->implode(' ');
@endphp

<div class="w-full">
    <label for="{{ $id }}" class="block text-[15px] font-semibold text-ink">
        {{ $label }}
        @if ($required)
            <span aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
        @endif
    </label>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1 text-[13px] text-muted">{{ $hint }}</p>
    @endif

    <input id="{{ $id }}"
           name="{{ $name }}"
           type="{{ $type }}"
           value="{{ old($name, $value) }}"
           @if ($required) required @endif
           @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if ($inputmode) inputmode="{{ $inputmode }}" @endif
           @if ($error) aria-invalid="true" @endif
           @if ($described) aria-describedby="{{ $described }}" @endif
           {{ $attributes->except('id')->merge(['class' => 'field mt-2']) }}>

    @if ($error)
        <p id="{{ $id }}-galat" class="mt-1.5 text-[13px] font-medium text-danger">{{ $error }}</p>
    @endif
</div>
