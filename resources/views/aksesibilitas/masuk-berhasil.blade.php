@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    <h1 class="text-[42px] font-bold leading-tight text-ink">Anda berhasil masuk</h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        Selamat datang kembali. Pilih tindakan berikutnya.
    </p>

    <div class="mt-10 flex flex-col gap-[25px]">
        <x-aks.tombol href="{{ route('beranda') }}">Lanjut ke beranda</x-aks.tombol>

        <x-aks.tombol warna="sekunder" href="{{ route('logout.konfirmasi') }}">Keluar dari akun</x-aks.tombol>
    </div>
@endsection
