@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Ringkasan toko"
        description="Informasi penjualan dan pesanan ditampilkan tanpa grafik yang ramai."
    />

    <div class="grid gap-5 md:grid-cols-3">
        @foreach (config('suaranniaga.ringkasan_toko') as $card)
            <div class="rounded-card border border-line bg-surface p-6">
                <h2 class="text-[18px] font-semibold text-ink">{{ $card['judul'] }}</h2>
                <p class="mt-2 text-[15px] text-muted">{{ $card['deskripsi'] }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mt-10 text-[22px] font-semibold text-ink">Aktivitas terbaru</h2>

    <ul class="mt-4 space-y-4">
        @foreach (config('suaranniaga.aktivitas') as $row)
            <li class="flex flex-wrap items-center gap-4 rounded-[12px] border border-line bg-surface px-6 py-5">
                <h3 class="w-[270px] shrink-0 text-[15px] font-semibold text-ink">{{ $row['id'] }}</h3>

                <p class="min-w-0 flex-1 text-[15px] text-body">{{ $row['produk'] }}</p>

                <x-status-pill tone="info">{{ $row['status'] }}</x-status-pill>
            </li>
        @endforeach
    </ul>
@endsection
