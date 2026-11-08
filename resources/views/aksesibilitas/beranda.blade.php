@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    <h1 class="text-[42px] font-bold leading-tight text-ink">Beranda pembeli</h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        Menu utama pembeli. Tidak ada kartu produk visual atau navigasi kecil.
    </p>

    <div class="mt-10 flex flex-col gap-[23px]">
        <x-aks.tombol href="{{ route('cari') }}">Cari produk dengan suara</x-aks.tombol>

        <x-aks.tombol warna="biru" href="{{ route('produk.daftar') }}">Dengarkan daftar produk</x-aks.tombol>

        <x-aks.tombol warna="biru" href="{{ route('keranjang') }}">Keranjang saya</x-aks.tombol>

        <x-aks.tombol warna="biru" href="{{ route('pesanan') }}">Pesanan saya</x-aks.tombol>

        <x-aks.tombol warna="sekunder" href="{{ route('profil') }}">Profil dan keluar</x-aks.tombol>
    </div>
@endsection
