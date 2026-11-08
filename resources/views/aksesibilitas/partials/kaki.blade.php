{{--
    Kaki halaman Mode Aksesibilitas: petunjuk navigasi keyboard dan tombol
    kembali ke mode normal. Petunjuk keyboard selalu terlihat karena mode ini
    dipakai pengguna yang mengandalkan navigasi keyboard.
--}}
@php
    $varian = $varian ?? 'a';
    $ukuran = $varian === 'a' ? 'text-[20px]' : 'text-[13px]';
    $hint = $varian === 'a'
        ? 'Navigasi: Tab = berikutnya  •  Shift + Tab = sebelumnya  •  Enter = pilih'
        : 'Navigasi: Tab = tombol berikutnya  •  Shift + Tab = tombol sebelumnya  •  Enter = pilih';
@endphp

<footer class="mt-auto pt-[60px]">
    <p class="font-medium text-muted {{ $ukuran }}">{{ $hint }}</p>

    <form method="POST" action="{{ route('mode.aksesibilitas', 'normal') }}" class="mt-5">
        @csrf
        <button type="submit" class="text-[18px] font-semibold text-brand underline underline-offset-4">
            Ganti ke mode normal
        </button>
    </form>
</footer>
