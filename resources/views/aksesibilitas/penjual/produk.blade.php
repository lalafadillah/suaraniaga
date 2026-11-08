@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    @php
        $daftar = $produk ?? collect();
        $jumlahProduk = $daftar->count();
        $halaman = max(1, (int) ($halaman ?? 1));
        $produkAktif = $daftar->get($halaman - 1);
        $sebelumnya = $halaman > 1 ? route('penjual.produk', ['halaman' => $halaman - 1]) : null;
        $berikutnya = $halaman < $jumlahProduk ? route('penjual.produk', ['halaman' => $halaman + 1]) : null;
    @endphp

    <h1 class="text-[42px] font-semibold leading-tight text-ink">Produk saya</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Dengarkan produk satu per satu, lalu pilih tindakan untuk mengelola produk tersebut.
    </p>

    <div class="mt-8">
        <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.dashboard') }}">Kembali ke ringkasan toko</x-aks.tombol>
        <p class="mt-2 text-[12px] text-muted">Enter = kembali ke ringkasan toko</p>
    </div>

    @if ($jumlahProduk === 0)
        <p class="mt-10 rounded-[16px] border border-line-strong bg-white p-[32px] text-[24px] text-ink">
            Anda belum memiliki produk. Tambahkan produk pertama Anda.
        </p>

        <div class="mt-8 flex flex-col gap-[18px]">
            <x-aks.tombol varian="b" href="{{ route('penjual.produk.tambah') }}">Tambah produk dengan suara</x-aks.tombol>
        </div>
    @else
        @php
            $ringkasan = 'Produk '.$produkAktif->name.'. Harga '.$produkAktif->price_text
                .'. Stok tersedia '.$produkAktif->stock.'. '
                .($produkAktif->tersedia() ? 'Status aktif dan tampil di katalog.' : 'Status stok habis, tidak tampil di katalog.');
        @endphp

        <section class="mt-10 rounded-[16px] border border-line-strong bg-white p-[32px]">
            <p class="text-[15px] font-semibold text-brand">
                PRODUK {{ $halaman }} DARI {{ $jumlahProduk }}
            </p>

            <h2 class="mt-3 text-[31px] font-semibold leading-tight text-ink">{{ $produkAktif->name }}</h2>

            <p class="mt-3 text-[22px] text-ink">Harga: {{ $produkAktif->price_text }}</p>
            <p class="mt-1 text-[22px] text-ink">Stok tersedia: {{ $produkAktif->stock }}</p>

            <p class="mt-3 text-[19px] font-semibold {{ $produkAktif->tersedia() ? 'text-success' : 'text-muted' }}">
                Status: {{ $produkAktif->tersedia() ? 'Aktif dan tampil di katalog' : 'Stok habis dan disembunyikan' }}
            </p>

            <p class="mt-3 text-[19px] leading-relaxed text-muted">{{ $produkAktif->description }}</p>
        </section>

        <div class="mt-8 flex flex-col gap-[18px] lg:flex-row lg:items-start lg:justify-between">
            <div class="flex flex-col gap-[18px] lg:flex-row lg:items-start">
                @if ($sebelumnya)
                    <div>
                        <x-aks.tombol varian="b" warna="sekunder" :href="$sebelumnya">Sebelumnya</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = sebelumnya</p>
                    </div>
                @endif

                <div>
                    <x-aks.tombol varian="b"
                                 type="button"
                                 x-on:click='$store.suara.speak(@js($ringkasan))'>
                        Dengarkan detail produk
                    </x-aks.tombol>
                    <p class="mt-2 text-[12px] text-muted">Enter = dengarkan detail produk</p>
                </div>

                @if ($berikutnya)
                    <div>
                        <x-aks.tombol varian="b" warna="sekunder" :href="$berikutnya">Berikutnya</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = berikutnya</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-[18px] flex flex-col gap-[18px] lg:flex-row lg:items-start">
            <div>
                <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.produk.ubah', $produkAktif) }}">
                    Ubah produk
                </x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Enter = ubah produk</p>
            </div>

            <div>
                <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.produk.hapus.konfirmasi', $produkAktif) }}">
                    Hapus produk
                </x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Enter = hapus produk</p>
            </div>
        </div>
    @endif
@endsection
