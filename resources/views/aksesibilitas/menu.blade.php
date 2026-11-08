@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    <h1 class="text-[42px] font-bold leading-tight text-ink">Selamat datang di SuaraNiaga</h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        Pilih satu tindakan. Setiap tombol dapat dijangkau berurutan menggunakan tombol Tab.
    </p>

    <div class="mt-10 flex flex-col gap-[23px]">
        <x-aks.tombol href="{{ route('login.masuk') }}">Masuk ke akun</x-aks.tombol>

        <x-aks.tombol warna="biru" href="{{ route('register.daftar') }}">Buat akun baru</x-aks.tombol>

        <x-aks.tombol warna="biru" href="{{ route('cari') }}">Jelajahi produk sebagai tamu</x-aks.tombol>

        <x-aks.tombol warna="sekunder" href="{{ route('pengaturan') }}">Bantuan aksesibilitas</x-aks.tombol>
    </div>
@endsection
