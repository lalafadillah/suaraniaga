@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Favorit"
        description="Produk yang kamu simpan supaya mudah ditemukan lagi."
    />

    @if (count($produk) === 0)
        <section class="rounded-card border border-line bg-surface p-8">
            <h2 class="text-[20px] font-semibold text-ink">Belum ada favorit</h2>
            <p class="mt-2 text-[15px] text-muted">
                Produk yang sering kamu lihat akan muncul di sini.
            </p>
            <div class="mt-6">
                <x-btn href="{{ route('cari') }}">Cari produk</x-btn>
            </div>
        </section>
    @else
        <ul class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($produk as $item)
                <li class="rounded-card border border-line bg-surface p-5">
                    <div class="flex h-28 items-center justify-center rounded-[10px] bg-brand-soft px-4">
                        <p class="text-center text-[13px] font-medium text-brand">Deskripsi produk tersedia dalam audio</p>
                    </div>

                    <h2 class="mt-4 text-[18px] font-semibold text-ink">{{ $item['nama'] }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ $item['ringkas'] }}</p>
                    <p class="mt-3 text-[18px] font-bold text-brand">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>

                    <div class="mt-4 flex gap-3">
                        <x-btn variant="ghost" size="sm" href="{{ route('produk.detail', $item['id']) }}">
                            Lihat {{ $item['nama'] }}
                        </x-btn>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
