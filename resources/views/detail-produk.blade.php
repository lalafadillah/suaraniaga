@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Detail produk"
        description="Informasi penting disusun berurutan dan dapat didengarkan."
    />

    <div class="grid gap-8 lg:grid-cols-[420px_1fr]">
        <div class="flex h-[450px] flex-col items-center justify-center rounded-panel bg-brand-soft px-10 text-center">
            <h2 class="text-[18px] font-semibold text-brand">Foto produk (opsional)</h2>
            <p class="mt-3 text-sm text-muted">Deskripsi audio menjadi sumber informasi utama.</p>
        </div>

        <div>
            <h2 class="text-[28px] font-bold text-ink">{{ $produk['nama'] }}</h2>

            <div class="mt-3">
                <x-status-pill tone="success">{{ $produk['stok'] }}</x-status-pill>
            </div>

            <p class="mt-5 text-[26px] font-bold text-brand">
                Rp {{ number_format($produk['harga'], 0, ',', '.') }}
            </p>

            <h3 class="mt-6 text-[18px] font-semibold text-ink">Deskripsi produk</h3>
            <p class="mt-2 text-base text-body">{{ $produk['deskripsi'] }}</p>

            <div class="mt-6 flex flex-wrap gap-3">
                <x-btn variant="soft" x-on:click="$store.suara.speak(@js($produk['audio']))">
                    Putar deskripsi audio
                </x-btn>

                <x-btn href="{{ route('keranjang') }}">Tambahkan ke keranjang</x-btn>
            </div>

            <div class="mt-6 rounded-[10px] border border-line-strong bg-brand-soft px-4 py-3">
                <h3 class="text-[16px] font-semibold text-ink">Deskripsi audio • dibacakan otomatis</h3>
                <p class="mt-1 text-[13px] text-muted-soft">
                    Nama, harga, dan deskripsi dibacakan melalui SpeechSynthesis.
                </p>
            </div>
        </div>
    </div>

    <section class="mt-8 rounded-card border border-line bg-surface p-6">
        <h2 class="text-[18px] font-semibold text-ink">Informasi penjual</h2>
        <p class="mt-2 text-[15px] text-muted">
            {{ $produk['toko'] }} • Rating {{ $produk['rating'] }} • Pengiriman dari {{ $produk['kota'] }}
        </p>
    </section>
@endsection
