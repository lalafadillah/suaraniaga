@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Keranjang saya"
        description="Periksa nama produk, jumlah, dan total sebelum melanjutkan."
    />

    @if (session('status'))
        <p role="status" class="mb-6 rounded-card border border-line bg-surface px-5 py-4 text-[15px] text-ink">
            {{ session('status') }}
        </p>
    @endif

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

    @if ($barang->isEmpty())
        <section class="rounded-card border border-line bg-surface p-8">
            <h2 class="text-[20px] font-semibold text-ink">Keranjang masih kosong</h2>
            <p class="mt-2 text-[15px] text-muted">
                Cari produk dulu, lalu tekan "Tambahkan ke keranjang" di halaman detail produk.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <x-btn href="{{ route('cari') }}">Cari produk</x-btn>
                <x-btn href="{{ route('beranda') }}" variant="outline">Kembali ke beranda</x-btn>
            </div>
        </section>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <div>
                <ul class="space-y-4">
                    @foreach ($barang as $row)
                        <li class="rounded-card border border-line bg-surface p-6">
                            <h2 class="text-[18px] font-semibold text-ink">{{ $row['nama'] }}</h2>

                            <p class="mt-1 text-[15px] text-muted">
                                Rp {{ number_format($row['harga'], 0, ',', '.') }}
                                <span aria-hidden="true">•</span>
                                Stok {{ $row['stok'] }}
                            </p>

                            <p class="mt-3 text-[15px] font-semibold text-brand">
                                Subtotal: Rp {{ number_format($row['subtotal'], 0, ',', '.') }}
                            </p>

                            <div class="mt-5 flex flex-wrap items-end gap-4">
                                <form method="POST"
                                      action="{{ route('keranjang.ubah', $row['produk']) }}"
                                      data-form-siap
                                      class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label for="jumlah-{{ $row['produk']->id }}"
                                               class="block text-[14px] font-semibold text-ink">
                                            Jumlah {{ $row['nama'] }}
                                        </label>
                                        <input id="jumlah-{{ $row['produk']->id }}"
                                               name="jumlah"
                                               type="number"
                                               inputmode="numeric"
                                               min="1"
                                               max="{{ min($row['stok'], 20) }}"
                                               value="{{ $row['jumlah'] }}"
                                               class="mt-2 h-[54px] w-[120px] rounded-[9px] border border-line bg-surface px-4 text-base text-body focus:border-brand">
                                    </div>

                                    <x-btn type="submit" variant="soft" size="sm">Simpan jumlah {{ $row['nama'] }}</x-btn>
                                </form>

                                <form method="POST" action="{{ route('keranjang.hapus', $row['produk']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-btn type="submit" variant="ghost" size="sm">
                                        Hapus {{ $row['nama'] }} dari keranjang
                                    </x-btn>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-8 max-w-[1000px] text-sm text-muted">
                    Jumlah bisa diubah dan barang bisa dihapus. Semua perubahan langsung tersimpan.
                </p>
            </div>

            <aside class="h-fit rounded-card border border-line bg-surface p-6" aria-labelledby="ringkasan-judul">
                <h2 id="ringkasan-judul" class="text-[20px] font-semibold text-ink">Ringkasan pesanan</h2>

                <dl class="mt-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <dt class="text-[15px] text-muted">Jumlah barang</dt>
                        <dd class="text-[15px] font-semibold text-body">{{ $barang->sum('jumlah') }}</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-[15px] text-muted">Subtotal</dt>
                        <dd class="text-[15px] font-semibold text-body">Rp {{ number_format($total, 0, ',', '.') }}</dd>
                    </div>

                    <hr class="border-line">

                    <div class="flex items-center justify-between">
                        <dt class="text-[18px] font-semibold text-ink">Total</dt>
                        <dd class="text-[18px] font-bold text-brand">Rp {{ number_format($total, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <x-btn href="{{ route('checkout') }}" class="w-full">Lanjut ke checkout</x-btn>
                </div>
            </aside>
        </div>
    @endif
@endsection
