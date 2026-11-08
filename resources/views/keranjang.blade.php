@extends('layouts.app')

@php
    $semuaProduk = collect(config('suaranniaga.produk'))->keyBy('id');
    $barang = collect(config('suaranniaga.keranjang'))
        ->map(function ($row) use ($semuaProduk) {
            $p = $semuaProduk[$row['produk_id']];

            return [
                'id' => $p['id'],
                'nama' => $p['nama'],
                'harga' => $p['harga'],
                'stok' => $p['stok'],
                'jumlah' => $row['jumlah'],
            ];
        })
        ->values();
@endphp

@section('content')
    <div x-data="{
        items: @js($barang),
        format(angka) {
            return 'Rp ' + angka.toLocaleString('id-ID');
        },
        get total() {
            return this.items.reduce((jumlah, item) => jumlah + item.harga * item.jumlah, 0);
        },
        ubah(index, selisih) {
            const item = this.items[index];
            const baru = Math.max(1, item.jumlah + selisih);

            item.jumlah = baru;
            this.$store.announcer.say(item.nama + ' jumlah ' + baru + ', total ' + this.format(this.total));
        },
        hapus(index) {
            const item = this.items[index];
            this.items.splice(index, 1);
            this.$store.announcer.say(item.nama + ' dihapus dari keranjang');
        },
    }">

        <x-page-heading
            title="Keranjang saya"
            description="Periksa nama produk, jumlah, dan total sebelum melanjutkan."
        />

        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <div>
                <ul class="space-y-4">
                    <template x-for="(item, index) in items" :key="item.id">
                        <li class="rounded-card border border-line bg-surface p-6">
                            <h2 class="text-[18px] font-semibold text-ink" x-text="item.nama"></h2>

                            <p class="mt-1 text-[15px] text-muted">
                                <span x-text="format(item.harga)"></span>
                                <span aria-hidden="true">•</span>
                                Jumlah <span x-text="item.jumlah"></span>
                            </p>

                            <div class="mt-4">
                                <x-status-pill tone="success" x-text="item.stok"></x-status-pill>
                            </div>

                            <div class="mt-5 flex flex-wrap items-center gap-3">
                                <x-btn variant="soft" size="sm"
                                       x-on:click="ubah(index, -1)"
                                       x-bind:aria-label="'Kurangi jumlah ' + item.nama">
                                    Kurangi
                                </x-btn>

                                <x-btn variant="soft" size="sm"
                                       x-on:click="ubah(index, 1)"
                                       x-bind:aria-label="'Tambah jumlah ' + item.nama">
                                    Tambah
                                </x-btn>

                                <x-btn variant="ghost" size="sm"
                                       x-on:click="hapus(index)"
                                       x-bind:aria-label="'Hapus ' + item.nama + ' dari keranjang'">
                                    Hapus
                                </x-btn>
                            </div>
                        </li>
                    </template>
                </ul>

                <p class="mt-8 max-w-[1000px] text-sm text-muted">
                    Aksi tersedia: tambah jumlah, kurangi jumlah, hapus barang, dan lanjut checkout. Setiap perubahan akan diumumkan.
                </p>
            </div>

            <aside class="h-fit rounded-card border border-line bg-surface p-6">
                <h2 class="text-[20px] font-semibold text-ink">Ringkasan pesanan</h2>

                <dl class="mt-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <dt class="text-[15px] text-muted">Subtotal</dt>
                        <dd class="text-[15px] font-semibold text-body" x-text="format(total)"></dd>
                    </div>

                    <hr class="border-line">

                    <div class="flex items-center justify-between">
                        <dt class="text-[18px] font-semibold text-ink">Total</dt>
                        <dd class="text-[18px] font-bold text-brand" x-text="format(total)"></dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <x-btn href="{{ route('checkout') }}" class="w-full">Lanjut ke checkout</x-btn>
                </div>
            </aside>
        </div>
    </div>
@endsection
