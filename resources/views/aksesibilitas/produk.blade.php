@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    @php
        $pemain = auth()->check();
        $harga = \App\Support\Rupiah::format($produk['harga']);
        $stok = (int) $produk['stok'];
        $deskripsi = (string) $produk['deskripsi'];
        $nama = (string) $produk['nama'];
    @endphp

    <h1 class="text-[40px] font-bold leading-tight text-ink">{{ $judul }}</h1>
    <p class="mt-3 text-[22px] leading-snug text-muted">{{ $instruksi }}</p>

    @if (! $produk)
        <p class="mt-10 rounded-[24px] border-2 border-line-strong bg-white p-[36px] text-[24px] text-ink">
            Tidak ada produk yang ditampilkan.
        </p>

        <div class="mt-8">
            <x-aks.tombol href="{{ route('beranda') }}">Kembali ke beranda</x-aks.tombol>
        </div>
    @else
    <section class="mt-10 rounded-[24px] border-2 border-line-strong bg-white p-[36px]">
        <p class="text-[18px] font-bold text-brand">{{ $posisi }}</p>
        <p class="mt-4 text-[16px] font-bold text-muted">{{ $labelKecil }}</p>

        <h2 class="mt-1 text-[38px] font-bold leading-tight text-ink">{{ $nama }}</h2>

        <p class="mt-3 text-[32px] font-bold text-success">{{ $harga }}</p>

        <p class="mt-3 text-[20px] font-medium text-muted">
            {{ $stok > 0 ? 'Tersedia' : 'Stok habis' }} &bull; {{ $stok }} stok
        </p>

        <p class="mt-1 text-[20px] font-medium text-ink">Dijual oleh {{ $produk['toko'] }}</p>

        <h3 class="mt-6 text-[18px] font-bold text-brand">Deskripsi produk</h3>
        <p class="mt-1 text-[22px] leading-relaxed text-ink">{{ $deskripsi }}</p>
    </section>

    <div class="mt-8 flex flex-col gap-[18px] xl:flex-row xl:items-start xl:justify-between">
        <div class="flex flex-col gap-[18px] xl:flex-row xl:items-start">
            @if ($sebelumnya)
                <div>
                    <x-aks.tombol warna="redup" :href="$sebelumnya">Sebelumnya</x-aks.tombol>
                    <p class="mt-2 text-[12px] text-muted">Tab / Shift+Tab = sebelumnya</p>
                </div>
            @endif

            <div>
                <x-aks.tombol type="button"
                             warna="sekunder"
                             x-on:click='$store.suara.speak(@js($deskripsi))'>
                    Dengarkan deskripsi
                </x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Enter = putar deskripsi</p>
            </div>

            <div>
                @if ($pemain)
                    <form method="POST" action="{{ route('keranjang.tambah', $produk['id']) }}" class="contents">
                        @csrf
                        <x-aks.tombol type="submit">Masukkan ke keranjang</x-aks.tombol>
                    </form>
                @else
                    <x-aks.tombol href="{{ route('login.masuk') }}">Masukkan ke keranjang</x-aks.tombol>
                @endif
                <p class="mt-2 text-[12px] text-muted">Enter = tambah ke keranjang</p>
            </div>

            <div>
                @if ($pemain)
                    <form method="POST" action="{{ route('products.order', $produk['id']) }}" class="contents">
                        @csrf
                        <x-aks.tombol type="submit" warna="sukses">Beli produk</x-aks.tombol>
                    </form>
                @else
                    <x-aks.tombol warna="sukses" href="{{ route('login.masuk') }}">Beli produk</x-aks.tombol>
                @endif
                <p class="mt-2 text-[12px] text-muted">Enter = beli sekarang</p>
            </div>
        </div>

        @if ($berikutnya)
            <div class="xl:text-right">
                <x-aks.tombol warna="sekunder" :href="$berikutnya">Berikutnya</x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Tab / Shift+Tab = berikutnya</p>
            </div>
        @endif
    </div>

    <p class="mt-8 text-[18px] font-medium text-muted">{{ $catatan }}</p>
    @endif
@endsection
