@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    @php
        $barang = $barang ?? collect();
        $total = $total ?? 0;
        $jumlahItem = $barang->count();
        $halaman = max(1, (int) ($halaman ?? 1));
        $item = $barang->get($halaman - 1);
        $sebelumnya = $halaman > 1 ? route('keranjang', ['halaman' => $halaman - 1]) : null;
        $berikutnya = $halaman < $jumlahItem ? route('keranjang', ['halaman' => $halaman + 1]) : null;
    @endphp

    <h1 class="text-[40px] font-bold leading-tight text-ink">Keranjang saya</h1>
    <p class="mt-3 text-[22px] leading-snug text-muted">
        Produk di keranjang ditampilkan satu per satu. Gunakan tombol navigasi untuk memeriksa setiap produk.
    </p>

    <x-aks.pesan class="mt-8" />

    @if ($jumlahItem === 0)
        <p class="mt-10 rounded-[24px] border-2 border-line-strong bg-white p-[36px] text-[24px] text-ink">
            Keranjang Anda masih kosong. Kembali ke beranda untuk mencari produk.
        </p>

        <div class="mt-8 flex flex-col gap-[18px]">
            <x-aks.tombol href="{{ route('beranda') }}">Kembali ke beranda</x-aks.tombol>
        </div>
    @else
        <section class="mt-10 rounded-[24px] border-2 border-line-strong bg-white p-[36px]">
            <p class="text-[18px] font-bold text-brand">
                ITEM {{ $halaman }} DARI {{ $jumlahItem }} &bull; KERANJANG
            </p>
            <p class="mt-4 text-[16px] font-bold text-muted">PRODUK DI KERANJANG</p>

            <h2 class="mt-1 text-[38px] font-bold leading-tight text-ink">{{ $item['nama'] }}</h2>

            <p class="mt-3 text-[32px] font-bold text-success">
                {{ \App\Support\Rupiah::format($item['subtotal']) }} &bull; Subtotal
            </p>

            <p class="mt-3 text-[20px] font-medium text-muted">Jumlah: {{ $item['jumlah'] }}</p>
            <p class="mt-1 text-[20px] font-medium text-ink">{{ $item['produk']->seller?->name ?? $item['toko'] ?? 'Toko SuaraNiaga' }}</p>

            <h3 class="mt-6 text-[18px] font-bold text-brand">Ringkasan produk</h3>
            <p class="mt-1 text-[22px] leading-relaxed text-ink">
                {{ $item['produk']->description }}. Produk ini sudah ditambahkan ke keranjang.
            </p>
        </section>

        <div class="mt-8 flex flex-col gap-[18px] xl:flex-row xl:items-start xl:justify-between">
            <div class="flex flex-col gap-[18px] xl:flex-row xl:items-start">
                @if ($sebelumnya)
                    <div>
                        <x-aks.tombol warna="redup" :href="$sebelumnya">Produk sebelumnya</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Tab / Shift+Tab = sebelumnya</p>
                    </div>
                @endif

                <div>
                    <form method="POST" action="{{ route('keranjang.ubah', $item['produk']) }}" class="contents">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="jumlah" value="{{ max(1, $item['jumlah'] - 1) }}">
                        <x-aks.tombol type="submit" warna="sekunder">Kurangi jumlah</x-aks.tombol>
                    </form>
                    <p class="mt-2 text-[12px] text-muted">Enter = kurangi jumlah</p>
                </div>

                <div>
                    <form method="POST" action="{{ route('keranjang.ubah', $item['produk']) }}" class="contents">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="jumlah" value="{{ $item['jumlah'] + 1 }}">
                        <x-aks.tombol type="submit" warna="sekunder">Tambah jumlah</x-aks.tombol>
                    </form>
                    <p class="mt-2 text-[12px] text-muted">Enter = tambah jumlah</p>
                </div>

                <div>
                    <x-aks.tombol warna="sukses" href="{{ route('checkout') }}">Beli produk</x-aks.tombol>
                    <p class="mt-2 text-[12px] text-muted">Enter = beli produk</p>
                </div>
            </div>

            @if ($berikutnya)
                <div class="xl:text-right">
                    <x-aks.tombol warna="sekunder" :href="$berikutnya">Produk berikutnya</x-aks.tombol>
                    <p class="mt-2 text-[12px] text-muted">Tab / Shift+Tab = berikutnya</p>
                </div>
            @endif
        </div>

        <p class="mt-8 text-[18px] font-medium text-muted">
            Gunakan tombol Kurangi jumlah atau Tambah jumlah untuk mengubah kuantitas. Beli produk untuk memesan.
        </p>
    @endif
@endsection
