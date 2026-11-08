@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Dasbor admin"
        description="Ringkasan jumlah pengguna, produk, dan pesanan yang tercatat di SuaraNiaga."
    />

    @include('admin.navigasi')

    @if (session('status'))
        <p role="status" class="mb-6 rounded-card border border-line bg-surface px-5 py-4 text-[15px] text-ink">
            {{ session('status') }}
        </p>
    @endif

    <h2 class="text-[22px] font-semibold text-ink">Jumlah pengguna</h2>

    <dl class="mt-4 grid gap-5 md:grid-cols-3">
        <div class="rounded-card border border-line bg-surface p-6">
            <dt class="text-[15px] font-semibold text-ink">Pembeli</dt>
            <dd class="mt-2 text-[28px] font-bold text-brand">{{ $jumlahPengguna['buyer'] }}</dd>
        </div>

        <div class="rounded-card border border-line bg-surface p-6">
            <dt class="text-[15px] font-semibold text-ink">Penjual</dt>
            <dd class="mt-2 text-[28px] font-bold text-brand">{{ $jumlahPengguna['seller'] }}</dd>
        </div>

        <div class="rounded-card border border-line bg-surface p-6">
            <dt class="text-[15px] font-semibold text-ink">Admin</dt>
            <dd class="mt-2 text-[28px] font-bold text-brand">{{ $jumlahPengguna['admin'] }}</dd>
        </div>
    </dl>

    <h2 class="mt-10 text-[22px] font-semibold text-ink">Jumlah produk dan pesanan</h2>

    <dl class="mt-4 grid gap-5 md:grid-cols-2">
        <div class="rounded-card border border-line bg-surface p-6">
            <dt class="text-[15px] font-semibold text-ink">Produk terdaftar</dt>
            <dd class="mt-2 text-[28px] font-bold text-brand">{{ $jumlahProduk }}</dd>
        </div>

        <div class="rounded-card border border-line bg-surface p-6">
            <dt class="text-[15px] font-semibold text-ink">Pesanan masuk</dt>
            <dd class="mt-2 text-[28px] font-bold text-brand">{{ $jumlahPesanan }}</dd>
        </div>
    </dl>

    <h2 class="mt-10 text-[22px] font-semibold text-ink">Pesanan per status</h2>

    <ul class="mt-4 space-y-3">
        @foreach ($statusPesanan as $status => $jumlah)
            <li class="flex flex-wrap items-center gap-4 rounded-card border border-line bg-surface px-6 py-4">
                <x-status-pill tone="info">{{ $status }}</x-status-pill>
                <span class="text-[15px] text-body">{{ $jumlah }} pesanan</span>
            </li>
        @endforeach
    </ul>
@endsection
