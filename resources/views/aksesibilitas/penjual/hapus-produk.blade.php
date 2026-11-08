@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    <h1 class="text-[42px] font-semibold leading-tight text-ink">Hapus produk</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Produk <span class="font-semibold text-ink">{{ $produk->name }}</span> akan dihapus dari katalog.
        Tindakan ini tidak dapat dibatalkan.
    </p>

    <x-aks.pesan class="mt-8" />

    <div class="mt-10 flex flex-col gap-[18px] lg:flex-row lg:items-start">
        <div>
            <form method="POST" action="{{ route('penjual.produk.hapus', $produk) }}">
                @csrf
                @method('DELETE')
                <x-aks.tombol varian="b" warna="sukses" type="submit">Ya, hapus produk</x-aks.tombol>
            </form>
            <p class="mt-2 text-[12px] text-muted">Enter = hapus produk</p>
        </div>

        <div>
            <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.produk') }}">Batal, kembali ke produk saya</x-aks.tombol>
            <p class="mt-2 text-[12px] text-muted">Enter = batal</p>
        </div>
    </div>
@endsection
