@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    @php
        $daftar = $pesanan ?? collect();
        $jumlahPesanan = $daftar->count();
        $halaman = max(1, (int) ($halaman ?? 1));
        $pesananAktif = $daftar->get($halaman - 1);
        $sebelumnya = $halaman > 1 ? route('penjual.pesanan', ['halaman' => $halaman - 1]) : null;
        $berikutnya = $halaman < $jumlahPesanan ? route('penjual.pesanan', ['halaman' => $halaman + 1]) : null;
    @endphp

    <h1 class="text-[42px] font-semibold leading-tight text-ink">Pesanan masuk</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Dengarkan pesanan baru satu per satu, periksa detailnya, lalu terima atau tolak pesanan.
    </p>

    @if ($jumlahPesanan === 0)
        <p class="mt-10 rounded-[16px] border border-line-strong bg-white p-[32px] text-[24px] text-ink">
            Belum ada pesanan masuk untuk toko Anda.
        </p>

        <div class="mt-8">
            <x-aks.tombol varian="b" href="{{ route('penjual.dashboard') }}">Kembali ke ringkasan toko</x-aks.tombol>
        </div>
    @else
        @php
            $ringkasan = 'Pesanan '.$pesananAktif->nomor.'. Produk '.$pesananAktif->product?->name
                .'. Jumlah '.$pesananAktif->quantity.'. Total '.$pesananAktif->total_text
                .'. Status '.$pesananAktif->status_label.'.';
        @endphp

        <section class="mt-10 rounded-[16px] border border-line-strong bg-white p-[32px]">
            <p class="text-[15px] font-semibold text-brand">
                PESANAN {{ $halaman }} DARI {{ $jumlahPesanan }}
            </p>

            <h2 class="mt-3 text-[29px] font-semibold leading-tight text-ink">
                Pesanan #{{ $pesananAktif->nomor }}
            </h2>

            <p class="mt-3 text-[21px] text-ink">Produk: {{ $pesananAktif->product?->name }}</p>
            <p class="mt-1 text-[21px] text-ink">Jumlah: {{ $pesananAktif->quantity }} produk</p>
            <p class="mt-1 text-[21px] text-ink">
                Total: {{ $pesananAktif->total_text }}
                &bull; Pembayaran: {{ \App\Models\Order::METODE_PEMBAYARAN[$pesananAktif->payment_method] ?? $pesananAktif->payment_method }}
            </p>
            <p class="mt-1 text-[18px] text-muted">Alamat pembeli: {{ $pesananAktif->shipping_address }}</p>
            <p class="mt-1 text-[18px] text-muted">Status saat ini: {{ $pesananAktif->status_label }}</p>
        </section>

        <div class="mt-8 flex flex-col gap-[18px] lg:flex-row lg:items-start lg:justify-between">
            <div class="flex flex-col gap-[18px] lg:flex-row lg:items-start">
                @if ($sebelumnya)
                    <div>
                        <x-aks.tombol varian="b" warna="sekunder" :href="$sebelumnya">Pesanan sebelumnya</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = pesanan sebelumnya</p>
                    </div>
                @endif

                <div>
                    <x-aks.tombol varian="b"
                                 type="button"
                                 x-on:click='$store.suara.speak(@js($ringkasan))'>
                        Dengarkan detail pesanan
                    </x-aks.tombol>
                    <p class="mt-2 text-[12px] text-muted">Enter = dengarkan detail pesanan</p>
                </div>

                @if ($berikutnya)
                    <div>
                        <x-aks.tombol varian="b" warna="sekunder" :href="$berikutnya">Pesanan berikutnya</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = pesanan berikutnya</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-[18px] flex flex-col gap-[18px] lg:flex-row lg:items-start">
            <div>
                <form method="POST" action="{{ route('penjual.pesanan.status', $pesananAktif) }}" class="contents">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_DIPROSES }}">
                    <x-aks.tombol varian="b" type="submit">Terima pesanan</x-aks.tombol>
                </form>
                <p class="mt-2 text-[12px] text-muted">Enter = terima pesanan</p>
            </div>

            <div>
                <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.pesanan.tolak.konfirmasi', $pesananAktif) }}">
                    Tolak pesanan
                </x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Enter = tolak pesanan</p>
            </div>
        </div>
    @endif
@endsection
