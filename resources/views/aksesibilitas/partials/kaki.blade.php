{{--
    Kaki halaman Mode Aksesibilitas: petunjuk navigasi keyboard, tombol kembali
    ke beranda, dan tombol keluar untuk penjual. Petunjuk keyboard selalu
    terlihat karena mode ini dipakai pengguna yang mengandalkan navigasi
    keyboard.
--}}
@php
    $varian = $varian ?? 'a';
    $ukuran = $varian === 'a' ? 'text-[20px]' : 'text-[13px]';
    $hint = $varian === 'a'
        ? 'Navigasi: Tab = berikutnya  •  Shift + Tab = sebelumnya  •  Enter = pilih'
        : 'Navigasi: Tab = tombol berikutnya  •  Shift + Tab = tombol sebelumnya  •  Enter = pilih';

    $pengguna = auth()->user();
    $penjual = (bool) $pengguna?->isSeller();
    $rumah = $penjual ? route('penjual.dashboard') : route('beranda');
    $labelRumah = $penjual ? 'Kembali ke ringkasan toko' : 'Kembali ke beranda';
@endphp

<footer class="mt-auto pt-[60px]">
    <p class="font-medium text-muted {{ $ukuran }}">{{ $hint }}</p>

    @if ($varian === 'a')
        <div class="mt-6 flex flex-col gap-[23px]">
            <x-aks.tombol warna="sekunder" :href="$rumah">{{ $labelRumah }}</x-aks.tombol>

            @if ($penjual)
                <x-aks.tombol warna="redup" :href="route('logout.konfirmasi')">Keluar dari akun</x-aks.tombol>
            @endif
        </div>
    @else
        <div class="mt-5 flex flex-wrap items-center gap-6">
            <a href="{{ $rumah }}" class="text-[18px] font-semibold text-brand underline underline-offset-4">
                {{ $labelRumah }}
            </a>

            @if ($penjual)
                <a href="{{ route('logout.konfirmasi') }}" class="text-[18px] font-semibold text-brand underline underline-offset-4">
                    Keluar dari akun
                </a>
            @endif

            <form method="POST" action="{{ route('mode.aksesibilitas', 'normal') }}">
                @csrf
                <button type="submit" class="text-[18px] font-semibold text-brand underline underline-offset-4">
                    Ganti ke mode normal
                </button>
            </form>
        </div>
    @endif
</footer>
