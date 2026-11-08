@extends('layouts.app')

@section('content')
    <div class="flex items-start justify-between gap-6">
        <x-page-heading
            title="Produk saya"
            description="Daftar produk aktif beserta harga, stok, dan statusnya."
        />

        <div class="flex shrink-0 flex-col items-end gap-3">
            <x-btn href="{{ route('penjual.produk.tambah') }}">Tambah produk dengan suara</x-btn>
            <x-btn href="{{ route('penjual.dashboard') }}" variant="outline">Kembali ke ringkasan toko</x-btn>
        </div>
    </div>

    @if (session('status'))
        <p role="status" class="mb-6 rounded-card border border-line bg-surface px-5 py-4 text-[15px] text-ink">
            {{ session('status') }}
        </p>
    @endif

    @if ($produk->isEmpty())
        <section class="rounded-card border border-line bg-surface p-8">
            <h2 class="text-[20px] font-semibold text-ink">Belum ada produk</h2>
            <p class="mt-2 text-[15px] text-muted">
                Post barang pertama kamu dengan suara, lalu pembeli bisa langsung memesan.
            </p>
            <div class="mt-6">
                <x-btn href="{{ route('penjual.produk.tambah') }}">Tambah produk dengan suara</x-btn>
            </div>
        </section>
    @else
        <ul class="space-y-4">
            @foreach ($produk as $item)
                <li class="flex flex-wrap items-center gap-4 rounded-card border border-line bg-surface px-6 py-5">
                    <h2 class="w-[360px] shrink-0 text-lg font-semibold text-ink">{{ $item->name }}</h2>

                    <p class="w-[150px] shrink-0 text-base font-medium text-brand">
                        Rp {{ number_format($item->price, 0, ',', '.') }}
                    </p>

                    <p class="w-[120px] shrink-0 text-sm text-muted">Stok {{ $item->stock }}</p>

                    <x-status-pill tone="{{ $item->tersedia() ? 'success' : 'warning' }}">
                        {{ $item->tersedia() ? 'Tersedia' : 'Stok habis' }}
                    </x-status-pill>

                    <div class="ml-auto flex gap-3">
                        <x-btn variant="ghost" size="sm" href="{{ route('produk.detail', $item->id) }}">
                            Lihat halaman produk {{ $item->name }}
                        </x-btn>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
