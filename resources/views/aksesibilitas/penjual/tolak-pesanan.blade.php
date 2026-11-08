@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    <h1 class="text-[42px] font-semibold leading-tight text-ink">Tolak pesanan</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Pesanan <span class="font-semibold text-ink">#{{ $pesanan->nomor }}</span> akan dibatalkan
        dan barang dikembalikan ke stok. Tindakan ini tidak dapat dibatalkan.
    </p>

    <x-aks.pesan class="mt-8" />

    <div class="mt-10 flex flex-col gap-[18px] lg:flex-row lg:items-start">
        <div>
            <form method="POST" action="{{ route('penjual.pesanan.tolak', $pesanan) }}">
                @csrf
                @method('DELETE')
                <x-aks.tombol varian="b" warna="sukses" type="submit">Ya, tolak pesanan</x-aks.tombol>
            </form>
            <p class="mt-2 text-[12px] text-muted">Enter = tolak pesanan</p>
        </div>

        <div>
            <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.pesanan') }}">Batal, kembali ke pesanan masuk</x-aks.tombol>
            <p class="mt-2 text-[12px] text-muted">Enter = batal</p>
        </div>
    </div>
@endsection
