@extends('layouts.app')

@section('content')
    <div class="flex items-start justify-between gap-6">
        <x-page-heading
            title="Produk saya"
            description="Daftar produk aktif beserta harga, stok, dan statusnya."
        />

        <x-btn href="{{ route('penjual.produk.tambah') }}" class="shrink-0">Tambah produk dengan suara</x-btn>
    </div>

    <ul class="space-y-4">
        @foreach (config('suaranniaga.produk') as $item)
            <li class="flex flex-wrap items-center gap-4 rounded-card border border-line bg-surface px-6 py-5">
                <h2 class="w-[360px] shrink-0 text-lg font-semibold text-ink">{{ $item['nama'] }}</h2>

                <p class="w-[150px] shrink-0 text-base font-medium text-brand">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>

                <p class="w-[120px] shrink-0 text-sm text-muted">Stok 24</p>

                <x-status-pill tone="success">Aktif</x-status-pill>

                <div class="ml-auto flex gap-3">
                    <x-btn variant="ghost" size="sm">Ubah</x-btn>
                    <x-btn variant="ghost" size="sm">Sembunyikan</x-btn>
                </div>
            </li>
        @endforeach
    </ul>
@endsection
