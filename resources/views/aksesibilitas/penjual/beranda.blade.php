@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    <h1 class="text-[40px] font-semibold leading-tight text-ink">Beranda penjual</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Menu utama penjual. Satu tindakan per layar, tanpa navigasi kecil.
    </p>

    <div class="mt-10 flex flex-col gap-[23px]">
        <x-aks.tombol varian="b" href="{{ route('penjual.produk.tambah') }}">Tambah produk dengan suara</x-aks.tombol>

        <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.produk') }}">Produk saya</x-aks.tombol>

        <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.pesanan') }}">Pesanan masuk</x-aks.tombol>

        <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.status') }}">Status pesanan</x-aks.tombol>

        <x-aks.tombol varian="b" warna="sekunder" href="{{ route('profil') }}">Profil toko dan keluar</x-aks.tombol>
    </div>
@endsection
