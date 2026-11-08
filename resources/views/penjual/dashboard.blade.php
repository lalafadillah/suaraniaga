@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Ringkasan toko"
        description="Informasi penjualan dan pesanan ditampilkan tanpa grafik yang ramai."
    />

    @if (session('status'))
        <p role="status" class="mb-6 rounded-card border border-line bg-surface px-5 py-4 text-[15px] text-ink">
            {{ session('status') }}
        </p>
    @endif

    <div class="grid gap-5 md:grid-cols-3">
        @foreach ($kartu as $card)
            <div class="rounded-card border border-line bg-surface p-6">
                <h2 class="text-[18px] font-semibold text-ink">{{ $card['judul'] }}</h2>
                <p class="mt-2 text-[15px] text-muted">{{ $card['deskripsi'] }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mt-10 text-[22px] font-semibold text-ink">Produk yang dijual</h2>

    @if ($produk->isEmpty())
        <p class="mt-4 text-[15px] text-muted">
            Belum ada produk. Post barang pertama kamu supaya pembeli bisa Pesan.
        </p>
    @else
        <ul class="mt-4 space-y-3">
            @foreach ($produk as $item)
                <li class="flex flex-wrap items-center gap-4 rounded-card border border-line bg-surface px-6 py-4">
                    <h3 class="min-w-0 flex-1 text-[15px] font-semibold text-ink">{{ $item->name }}</h3>

                    <p class="text-[15px] font-medium text-brand">
                        Rp {{ number_format($item->price, 0, ',', '.') }}
                    </p>

                    <p class="text-sm text-muted">Stok {{ $item->stock }}</p>

                    <x-status-pill tone="{{ $item->tersedia() ? 'success' : 'warning' }}">
                        {{ $item->tersedia() ? 'Tersedia' : 'Stok habis' }}
                    </x-status-pill>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            <x-btn href="{{ route('penjual.produk') }}" variant="outline">Kelola semua produk</x-btn>
        </div>
    @endif

    <h2 class="mt-10 text-[22px] font-semibold text-ink">Pesanan terbaru</h2>

    @if ($pesanan->isEmpty())
        <p class="mt-4 text-[15px] text-muted">
            Belum ada pesanan masuk untuk produk kamu.
        </p>
    @else
        <ul class="mt-4 space-y-4">
            @foreach ($pesanan as $order)
                <li class="flex flex-wrap items-center gap-4 rounded-[12px] border border-line bg-surface px-6 py-5">
                    <h3 class="w-[270px] shrink-0 text-[15px] font-semibold text-ink">{{ $order->nomor }}</h3>

                    <p class="min-w-0 flex-1 text-[15px] text-body">
                        {{ $order->product?->name }} × {{ $order->quantity }}
                    </p>

                    <x-status-pill tone="info">{{ $order->status_label }}</x-status-pill>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            <x-btn href="{{ route('penjual.pesanan') }}" variant="outline">Kelola semua pesanan</x-btn>
        </div>
    @endif
@endsection
