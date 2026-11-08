@extends('layouts.app')

@section('content')
    <div x-data x-init="$store.announcer.say('Pesanan berhasil dibuat. Nomor pesanan SN 2026 00124.')">
        <x-page-heading
            title="Pesanan berhasil dibuat"
            description="Konfirmasi ditampilkan secara visual dan diumumkan oleh pembaca layar."
        />

        <section class="rounded-panel border border-line bg-surface p-10" aria-labelledby="konfirmasi-judul">
            <x-status-pill tone="success">Pesanan dikonfirmasi</x-status-pill>

            <h2 id="konfirmasi-judul" class="mt-5 text-[28px] font-bold text-ink">
                Terima kasih, pesananmu sudah tercatat.
            </h2>

            <p class="mt-3 text-lg font-medium text-body">Nomor pesanan: SN-2026-00124</p>

            <p class="mt-2 text-base text-muted">
                Total: Rp 106.000 <span aria-hidden="true">•</span> Status: Menunggu penjual memproses
            </p>

            <div class="mt-10 flex flex-wrap gap-4">
                <x-btn href="{{ route('pesanan.sukses') }}">Lihat pesanan</x-btn>

                <x-btn href="{{ route('beranda') }}" variant="outline">Kembali ke beranda</x-btn>
            </div>
        </section>
    </div>
@endsection
