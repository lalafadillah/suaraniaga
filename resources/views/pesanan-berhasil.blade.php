@extends('layouts.app')

@section('content')
    @php
        $daftarPesanan = $orders ?? collect();
        $pertama = $order ?? $daftarPesanan->first();
        $pengumuman = $pertama
            ? 'Pesanan berhasil dibuat. Nomor pesanan '.$pertama->nomor.'.'
            : 'Pesanan berhasil dibuat.';
    @endphp

    <div x-data x-init="$store.announcer.say(@js($pengumuman))">
        <x-page-heading
            title="Pesanan berhasil dibuat"
            description="Konfirmasi ditampilkan secara visual dan diumumkan oleh pembaca layar."
        />

        <section class="rounded-panel border border-line bg-surface p-10" aria-labelledby="konfirmasi-judul">
            <x-status-pill tone="success">Pesanan dikonfirmasi</x-status-pill>

            <h2 id="konfirmasi-judul" class="mt-5 text-[28px] font-bold text-ink">
                Terima kasih, pesananmu sudah tercatat.
            </h2>

            <ul class="mt-6 space-y-4">
                @foreach ($daftarPesanan as $baris)
                    <li class="rounded-card border border-line bg-surface p-5">
                        <p class="text-lg font-medium text-body">Nomor pesanan: {{ $baris->nomor }}</p>

                        <p class="mt-2 text-base text-ink">
                            {{ $baris->product?->name }} × {{ $baris->quantity }}
                        </p>

                        <p class="mt-1 text-base text-muted">
                            Total: Rp {{ number_format($baris->total_price, 0, ',', '.') }}
                            <span aria-hidden="true">•</span>
                            Status: {{ $baris->status_label }}
                        </p>

                        @if ($baris->shipping_address)
                            <p class="mt-1 text-sm text-muted">
                                Dikirim ke {{ $baris->recipient_name }} — {{ $baris->shipping_address }}
                            </p>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-10 flex flex-wrap gap-4">
                <x-btn href="{{ route('pesanan') }}">Lihat pesanan saya</x-btn>

                <x-btn href="{{ route('beranda') }}" variant="outline">Kembali ke beranda</x-btn>
            </div>
        </section>
    </div>
@endsection
