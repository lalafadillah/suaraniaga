@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    @php
        $barang = $barang ?? collect();
        $total = $total ?? 0;
        $pengguna = auth()->user();
        $metode = old('metode', 'ditempat');
        $labelMetode = \App\Models\Order::METODE_PEMBAYARAN[$metode] ?? 'Simulasi pembayaran';
    @endphp

    <h1 class="text-[40px] font-semibold leading-tight text-ink">Checkout</h1>
    <p class="mt-3 text-[22px] leading-snug text-muted">
        Periksa alamat dan ringkasan belanja sebelum membuat pesanan.
    </p>

    <x-aks.pesan class="mt-8" />

    <form method="POST" action="{{ route('checkout.proses') }}" class="mt-10" data-form-siap>
        @csrf

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-[14px] border border-line bg-white p-[28px]">
                <h2 class="text-[17px] font-bold text-brand">RINGKASAN PRODUK</h2>

                @forelse ($barang as $item)
                    <div class="mt-4">
                        <p class="text-[32px] font-bold leading-tight text-ink">{{ $item['nama'] }}</p>
                        <p class="mt-1 text-[21px] font-medium text-muted">
                            {{ $item['produk']->seller?->name ?? 'Toko SuaraNiaga' }}
                        </p>
                        <p class="mt-1 text-[21px] text-ink">
                            Jumlah: {{ $item['jumlah'] }} &bull; Harga satuan: {{ \App\Support\Rupiah::format($item['harga']) }}
                        </p>
                    </div>
                @empty
                    <p class="mt-4 text-[21px] text-ink">Keranjang masih kosong.</p>
                @endforelse
            </section>

            <section class="rounded-[14px] border border-line bg-white p-[28px]">
                <h2 class="text-[17px] font-bold text-brand">ALAMAT PENGIRIMAN</h2>

                <div class="mt-4 space-y-5">
                    <x-aks.field name="nama"
                                 label="Nama penerima"
                                 type="text"
                                 :value="old('nama', $pengguna?->name)"
                                 placeholder="Ketik nama penerima"
                                 required />

                    <x-aks.field name="telepon"
                                 label="Nomor telepon"
                                 type="tel"
                                 :value="old('telepon', $pengguna?->phone)"
                                 placeholder="Ketik nomor telepon" />

                    <x-aks.field name="alamat"
                                 label="Alamat lengkap"
                                 type="text"
                                 :value="old('alamat', $pengguna?->address)"
                                 placeholder="Ketik alamat lengkap"
                                 required />

                    <x-aks.field name="catatan"
                                 label="Catatan untuk penjual"
                                 type="text"
                                 :value="old('catatan')"
                                 placeholder="Opsional" />
                </div>
            </section>

            <section class="rounded-[14px] border border-line bg-white p-[28px]">
                <h2 class="text-[17px] font-bold text-brand">METODE PEMBAYARAN</h2>

                <div class="mt-4 flex items-center gap-4">
                    <span aria-hidden="true"
                          class="flex h-[48px] w-[48px] items-center justify-center rounded-[10px] bg-brand-soft text-[20px] font-bold text-success">
                        Rp
                    </span>

                    <div>
                        <label for="metode" class="block text-[24px] font-bold text-ink">Metode pembayaran</label>
                        <select id="metode"
                                name="metode"
                                class="mt-2 h-[68px] w-full rounded-[12px] border border-line-strong bg-white px-4 text-[23px] text-ink">
                            @foreach (\App\Models\Order::METODE_PEMBAYARAN as $nilai => $judul)
                                <option value="{{ $nilai }}" @selected($metode === $nilai)>{{ $judul }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-[18px] text-muted">Pembayaran dilakukan secara simulasi untuk demo.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[14px] border border-line bg-white p-[28px]">
                <h2 class="text-[17px] font-bold text-brand">RINCIAN PEMBAYARAN</h2>

                <div class="mt-4 flex items-center justify-between text-[19px] text-ink">
                    <span>Subtotal produk</span>
                    <span class="font-medium">{{ \App\Support\Rupiah::format($total) }}</span>
                </div>

                <hr class="my-4 border-line">

                <div class="flex items-center justify-between">
                    <span class="text-[20px] font-bold text-ink">Total yang harus dibayar</span>
                    <span class="text-[26px] font-bold text-success">{{ \App\Support\Rupiah::format($total) }}</span>
                </div>
            </section>
        </div>

        <div class="mt-10 flex flex-col gap-[18px] lg:flex-row lg:items-start lg:justify-between">
            <div>
                <x-aks.tombol varian="b" warna="sekunder" href="{{ route('keranjang') }}">Kembali ke keranjang</x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Tab / Shift+Tab = navigasi tombol</p>
            </div>

            <div>
                <x-aks.tombol varian="b"
                             type="button"
                             x-on:click='$store.suara.speak(@js("Total yang harus dibayar ".\App\Support\Rupiah::format($total).". Metode pembayaran ".$labelMetode."."))'>
                    Dengarkan ringkasan
                </x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Enter = dengarkan ringkasan</p>
            </div>

            <div>
                <x-aks.tombol varian="b" warna="sukses" type="submit">Konfirmasi pesanan</x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Enter = buat pesanan dan lanjut ke Pesanan Saya</p>
            </div>
        </div>
    </form>
@endsection
