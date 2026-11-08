@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    @php
        $daftar = $pesanan ?? collect();
        $jumlahPesanan = $daftar->count();
        $halaman = max(1, (int) ($halaman ?? 1));
        $pesananAktif = $daftar->get($halaman - 1);
        $sebelumnya = $halaman > 1 ? route('penjual.status', ['halaman' => $halaman - 1]) : null;
        $berikutnya = $halaman < $jumlahPesanan ? route('penjual.status', ['halaman' => $halaman + 1]) : null;
    @endphp

    <h1 class="text-[42px] font-semibold leading-tight text-ink">Status pesanan</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Perbarui perkembangan pesanan agar pembeli dapat mendengarkan status terbarunya.
    </p>

    <div class="mt-8">
        <x-aks.tombol varian="b" warna="sekunder" href="{{ route('penjual.dashboard') }}">Kembali ke ringkasan toko</x-aks.tombol>
        <p class="mt-2 text-[12px] text-muted">Enter = kembali ke ringkasan toko</p>
    </div>

    @if ($jumlahPesanan === 0)
        <p class="mt-10 rounded-[16px] border border-line-strong bg-white p-[32px] text-[24px] text-ink">
            Belum ada pesanan yang perlu diperbarui statusnya.
        </p>

        <div class="mt-8">
            <x-aks.tombol varian="b" href="{{ route('penjual.dashboard') }}">Kembali ke ringkasan toko</x-aks.tombol>
        </div>
    @else
        @php
            $pembeli = $pesananAktif->buyer?->name ?? 'Pembeli';
            $penerima = $pesananAktif->recipient_name ?: $pembeli;
            $ringkasan = 'Pesanan '.$pesananAktif->nomor.'. Produk '.$pesananAktif->product?->name
                .'. Pembeli '.$pembeli.'. Penerima '.$penerima
                .'. Status saat ini '.$pesananAktif->status_label
                .'. Total '.$pesananAktif->total_text.'.';
        @endphp

        <section class="mt-10 rounded-[16px] border border-line-strong bg-white p-[32px]">
            <p class="text-[15px] font-semibold text-brand">
                PESANAN {{ $halaman }} DARI {{ $jumlahPesanan }} &bull; #{{ $pesananAktif->nomor }}
            </p>

            <h2 class="mt-3 text-[25px] font-semibold text-ink">
                {{ $pesananAktif->product?->name }} &bull; {{ $pesananAktif->quantity }} produk
            </h2>

            <p class="mt-3 text-[19px] text-ink">Pembeli: {{ $pembeli }}</p>
            <p class="mt-1 text-[19px] text-ink">Penerima: {{ $penerima }}</p>

            <p class="mt-1 text-[22px] font-semibold text-accent">
                Status saat ini: {{ $pesananAktif->status_label }}
            </p>

            <p class="mt-1 text-[19px] text-muted">
                Riwayat: {{ $pesananAktif->status_label }}
            </p>

            <p class="mt-1 text-[18px] text-ink">
                Pembayaran: {{ \App\Models\Order::METODE_PEMBAYARAN[$pesananAktif->payment_method] ?? $pesananAktif->payment_method }}
                &bull; Total: {{ $pesananAktif->total_text }}
            </p>
        </section>

        <div class="mt-8 flex flex-col gap-[18px] lg:flex-row lg:items-start">
            <div>
                <x-aks.tombol varian="b"
                             type="button"
                             x-on:click='$store.suara.speak(@js($ringkasan))'>
                    Dengarkan detail pesanan
                </x-aks.tombol>
                <p class="mt-2 text-[12px] text-muted">Enter = dengarkan detail pesanan</p>
            </div>

            @if ($sebelumnya)
                <div>
                    <x-aks.tombol varian="b" warna="sekunder" :href="$sebelumnya">Lihat pesanan sebelumnya</x-aks.tombol>
                    <p class="mt-2 text-[12px] text-muted">Enter = lihat pesanan sebelumnya</p>
                </div>
            @endif

            @if ($berikutnya)
                <div>
                    <x-aks.tombol varian="b" warna="sekunder" :href="$berikutnya">Lihat pesanan berikutnya</x-aks.tombol>
                    <p class="mt-2 text-[12px] text-muted">Enter = lihat pesanan berikutnya</p>
                </div>
            @endif
        </div>

        <div class="mt-[18px] flex flex-col gap-[18px] lg:flex-row lg:items-start">
            @if ($pesananAktif->bisaMajuKe(\App\Models\Order::STATUS_DIKIRIM))
                <div>
                    <form method="POST" action="{{ route('penjual.pesanan.status', $pesananAktif) }}" class="contents">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_DIKIRIM }}">
                        <x-aks.tombol varian="b" type="submit">Tandai sedang dikirim</x-aks.tombol>
                    </form>
                    <p class="mt-2 text-[12px] text-muted">Enter = tandai sedang dikirim</p>
                </div>
            @endif

            @if ($pesananAktif->bisaMajuKe(\App\Models\Order::STATUS_SELESAI))
                <div>
                    <form method="POST" action="{{ route('penjual.pesanan.status', $pesananAktif) }}" class="contents">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_SELESAI }}">
                        <x-aks.tombol varian="b" warna="sekunder" type="submit">Tandai pesanan selesai</x-aks.tombol>
                    </form>
                    <p class="mt-2 text-[12px] text-muted">Enter = tandai pesanan selesai</p>
                </div>
            @endif
        </div>
    @endif
@endsection
