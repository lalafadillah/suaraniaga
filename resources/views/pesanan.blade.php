@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Pesanan saya"
        description="Daftar pesanan beserta status pengiriman, diurutkan dari yang terbaru."
    />

    <ul class="space-y-4">
        @foreach ($pesanan as $row)
            <li class="rounded-card border border-line bg-surface p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-[15px] font-semibold text-ink">{{ $row['id'] }}</h2>

                    <x-status-pill tone="{{ $row['status'] === 'Selesai' ? 'success' : 'info' }}">{{ $row['status'] }}</x-status-pill>
                </div>

                <p class="mt-2 text-[15px] text-body">{{ $row['produk'] }}</p>
                <p class="mt-1 text-sm text-muted">{{ $row['helper'] }}</p>

                <div class="mt-4">
                    <x-btn variant="ghost" size="sm">{{ $row['aksi'] }}</x-btn>
                </div>
            </li>
        @endforeach
    </ul>
@endsection
