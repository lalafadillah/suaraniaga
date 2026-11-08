@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Pesanan masuk"
        description="Antrean pesanan toko, diurutkan dari yang perlu diproses lebih dulu."
    />

    <ul class="space-y-4">
        @foreach (config('suaranniaga.pesanan') as $row)
            <li class="rounded-card border border-line bg-surface px-6 py-5">
                <div class="flex flex-wrap items-center gap-4">
                    <h2 class="w-[270px] shrink-0 text-[15px] font-semibold text-ink">{{ $row['id'] }}</h2>

                    <p class="min-w-0 flex-1 text-[15px] text-body">{{ $row['produk'] }}</p>

                    <x-status-pill tone="info">{{ $row['status'] }}</x-status-pill>
                </div>

                <p class="mt-2 text-sm text-muted">{{ $row['helper'] }}</p>

                <div class="mt-4 flex flex-wrap gap-3">
                    <x-btn variant="outline" size="sm">Terima pesanan</x-btn>
                    <x-btn variant="ghost" size="sm">Batalkan</x-btn>
                </div>
            </li>
        @endforeach
    </ul>
@endsection
