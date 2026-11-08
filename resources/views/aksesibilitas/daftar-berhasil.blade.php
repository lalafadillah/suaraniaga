@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    <h1 class="text-[42px] font-bold leading-tight text-ink">Akun berhasil dibuat</h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        Pendaftaran selesai. Anda dapat melanjutkan ke halaman utama.
    </p>

    <div class="mt-10 flex flex-col gap-[25px]">
        <x-aks.tombol href="{{ route('beranda') }}">Masuk ke SuaraNiaga</x-aks.tombol>

        <x-aks.tombol warna="sekunder" href="{{ route('beranda') }}">Kembali ke menu utama</x-aks.tombol>
    </div>
@endsection
