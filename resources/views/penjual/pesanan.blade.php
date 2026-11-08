@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Pesanan masuk"
        description="Antrean pesanan toko, diurutkan dari yang perlu diproses lebih dulu."
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

    @if ($pesanan->isEmpty())
        <section class="rounded-card border border-line bg-surface p-8">
            <h2 class="text-[20px] font-semibold text-ink">Belum ada pesanan masuk</h2>
            <p class="mt-2 text-[15px] text-muted">
                Begitu pembeli memesan produk kamu, pesanan itu muncul di halaman ini.
            </p>
        </section>
    @else
        <ul class="space-y-4">
            @foreach ($pesanan as $order)
                <li class="rounded-card border border-line bg-surface px-6 py-5">
                    <div class="flex flex-wrap items-center gap-4">
                        <h2 class="w-[270px] shrink-0 text-[15px] font-semibold text-ink">{{ $order->nomor }}</h2>

                        <p class="min-w-0 flex-1 text-[15px] text-body">
                            {{ $order->product?->name }} × {{ $order->quantity }}
                        </p>

                        <x-status-pill tone="{{ $order->status === 'selesai' ? 'success' : 'info' }}">
                            {{ $order->status_label }}
                        </x-status-pill>
                    </div>

                    <p class="mt-2 text-sm text-muted">
                        {{ $order->buyer?->name }} • Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </p>

                    @if ($order->shipping_address)
                        <p class="mt-1 text-sm text-muted">
                            Dikirim ke {{ $order->recipient_name }} — {{ $order->shipping_address }}
                        </p>
                    @endif

                    @if ($order->note)
                        <p class="mt-1 text-sm text-muted">Catatan pembeli: {{ $order->note }}</p>
                    @endif

                    @if ($order->statusBerikutnya())
                        <form method="POST"
                              action="{{ route('penjual.pesanan.status', $order) }}"
                              data-form-siap
                              class="mt-4">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $order->statusBerikutnya() }}">

                            <x-btn type="submit" variant="outline" size="sm">
                                {{ $order->statusBerikutnya() === 'diproses' ? 'Terima pesanan' : 'Ubah status menjadi '.\App\Models\Order::LABEL_STATUS[$order->statusBerikutnya()] }}
                                {{ $order->nomor }}
                            </x-btn>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-muted">Pesanan ini sudah selesai.</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
@endsection
