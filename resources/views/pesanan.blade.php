@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Pesanan saya"
        description="Daftar pesanan beserta status pengiriman, diurutkan dari yang terbaru."
    />

    @if ($pesanan->isEmpty())
        <section class="rounded-card border border-line bg-surface p-8">
            <h2 class="text-[20px] font-semibold text-ink">Belum ada pesanan</h2>
            <p class="mt-2 text-[15px] text-muted">
                Pesanan yang kamu buat akan muncul di sini lengkap dengan statusnya.
            </p>
            <div class="mt-6">
                <x-btn href="{{ route('cari') }}">Cari produk</x-btn>
            </div>
        </section>
    @else
        <ul class="space-y-4">
            @foreach ($pesanan as $order)
                <li class="rounded-card border border-line bg-surface p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <h2 class="text-[15px] font-semibold text-ink">{{ $order->nomor }}</h2>

                        <x-status-pill tone="{{ $order->status === 'selesai' ? 'success' : 'info' }}">
                            {{ $order->status_label }}
                        </x-status-pill>
                    </div>

                    <p class="mt-2 text-[15px] text-body">
                        {{ $order->product?->name }} × {{ $order->quantity }}
                    </p>

                    <p class="mt-1 text-sm text-muted">
                        Total Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        <span aria-hidden="true">•</span>
                        {{ $order->created_at?->format('d M Y H:i') }}
                    </p>

                    @if ($order->recipient_name)
                        <p class="mt-1 text-sm text-muted">
                            Dikirim ke {{ $order->recipient_name }}
                            @if ($order->shipping_address) — {{ $order->shipping_address }} @endif
                        </p>
                    @endif

                    @if ($order->note)
                        <p class="mt-1 text-sm text-muted">Catatan: {{ $order->note }}</p>
                    @endif

                    <div class="mt-4">
                        <x-btn variant="ghost" size="sm" href="{{ route('produk.detail', $order->product_id) }}">
                            Lihat {{ $order->product?->name }}
                        </x-btn>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
