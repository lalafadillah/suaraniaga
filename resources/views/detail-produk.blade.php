@extends('layouts.app')

@section('content')
    @php
        $bisaBeli = auth()->user()?->role === 'buyer';
        $stok = (int) $produk['stok'];
    @endphp

    <x-page-heading
        title="Detail produk"
        description="Informasi penting disusun berurutan dan dapat didengarkan."
    />

    @if ($errors->any())
        <div id="ringkasan-galat"
             role="alert"
             tabindex="-1"
             class="mb-6 rounded-card border border-line bg-surface px-5 py-4">
            <h2 class="text-[17px] font-semibold text-ink">Ada kesalahan</h2>
            <ul class="mt-2 list-disc pl-6 text-[15px] text-body">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[420px_1fr]">
        <div class="flex h-[450px] flex-col items-center justify-center rounded-panel bg-brand-soft px-10 text-center">
            <h2 class="text-[18px] font-semibold text-brand">Foto produk (opsional)</h2>
            <p class="mt-3 text-sm text-muted">Deskripsi audio menjadi sumber informasi utama.</p>
        </div>

        <div>
            <h2 class="text-[28px] font-bold text-ink">{{ $produk['nama'] }}</h2>

            <div class="mt-3">
                <x-status-pill tone="{{ $stok > 0 ? 'success' : 'warning' }}">
                    {{ $stok > 0 ? 'Stok '.$stok.' tersedia' : 'Stok habis' }}
                </x-status-pill>
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
            </div>

            @if (! $bisaBeli)
                <div class="mt-6 rounded-[10px] border border-line-strong bg-brand-soft p-5">
                    <h3 class="text-[16px] font-semibold text-ink">Beli sebagai akun pembeli</h3>
                    <p class="mt-1 text-[14px] text-muted">
                        Masuk dulu supaya pesanan bisa dikirim ke penjual.
                    </p>
                    <div class="mt-4">
                        <x-btn href="{{ route('login') }}" variant="outline">Masuk untuk membeli</x-btn>
                    </div>
                </div>
            @elseif ($stok < 1)
                <div class="mt-6 rounded-[10px] border border-line-strong bg-amber-soft p-5">
                    <h3 class="text-[16px] font-semibold text-ink">Stok produk ini habis</h3>
                    <p class="mt-1 text-[14px] text-muted">
                        Penjual belum menambah stok. Cek lagi nanti atau cari produk lain.
                    </p>
                </div>
            @else
                <div class="mt-6 rounded-[10px] border border-line-strong bg-brand-soft p-5">
                    <h3 class="text-[16px] font-semibold text-ink">Pesan produk ini</h3>

                    <form method="POST"
                          action="{{ route('products.order', $produk['id']) }}"
                          data-form-siap
                          class="mt-4 flex flex-wrap items-end gap-4">
                        @csrf

                        <div>
                            <label for="jumlah-beli" class="block text-[14px] font-semibold text-ink">Jumlah</label>
                            <input id="jumlah-beli"
                                   name="quantity"
                                   type="number"
                                   inputmode="numeric"
                                   min="1"
                                   max="{{ $stok }}"
                                   value="1"
                                   @if ($errors->has('quantity')) aria-invalid="true" aria-describedby="jumlah-beli-galat" @endif
                                   class="mt-2 h-[54px] w-[120px] rounded-[9px] border border-line bg-surface px-4 text-base text-body focus:border-brand">
                        </div>

                        <x-btn type="submit">Beli {{ $produk['nama'] }}</x-btn>
                    </form>

                    @if ($errors->has('quantity'))
                        <p id="jumlah-beli-galat" class="mt-2 text-[13px] font-medium text-danger">
                            {{ $errors->first('quantity') }}
                        </p>
                    @endif

                    <form method="POST" action="{{ route('keranjang.tambah', $produk['id']) }}" class="mt-4">
                        @csrf
                        <x-btn type="submit" variant="outline">Tambahkan {{ $produk['nama'] }} ke keranjang</x-btn>
                    </form>
                </div>
            @endif

            <div class="mt-6 rounded-[10px] border border-line-strong bg-brand-soft p-4 py-3">
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
