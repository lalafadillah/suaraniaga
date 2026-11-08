@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    @php
        $daftar = $pesanan ?? collect();
        $jumlahPesanan = $daftar->count();
        $halaman = max(1, (int) ($halaman ?? 1));
        $pesananAktif = $daftar->get($halaman - 1);
        $sebelumnya = $halaman > 1 ? route('pesanan', ['halaman' => $halaman - 1]) : null;
        $berikutnya = $halaman < $jumlahPesanan ? route('pesanan', ['halaman' => $halaman + 1]) : null;

        $langkah = [
            ['judul' => 'Pesanan dibuat', 'sub' => 'Pesanan diterima'],
            ['judul' => 'Sedang diproses', 'sub' => 'Pesanan dikemas'],
            ['judul' => 'Sedang dikirim', 'sub' => 'Status saat ini'],
            ['judul' => 'Pesanan selesai', 'sub' => 'Belum selesai'],
        ];

        $urutan = \App\Models\Order::URUTAN_STATUS;
        $posisiStatus = $pesananAktif
            ? (int) array_search($pesananAktif->status, $urutan, true)
            : 0;
    @endphp

    <h1 class="text-[40px] font-semibold leading-tight text-ink">Pesanan Saya</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Dengarkan detail pesanan dan periksa perjalanan pesanan. Satu pesanan ditampilkan dalam satu halaman.
    </p>

    @if ($jumlahPesanan === 0)
        <p class="mt-10 rounded-[20px] border border-line bg-white p-[32px] text-[24px] text-ink">
            Belum ada pesanan. Kembali ke beranda untuk mencari produk.
        </p>

        <div class="mt-8">
            <x-aks.tombol varian="b" href="{{ route('beranda') }}">Kembali ke beranda</x-aks.tombol>
        </div>
    @else
        @php
            $ringkasan = 'Pesanan '.$pesananAktif->nomor.'. '.$pesananAktif->product?->name
                .'. Status '.$pesananAktif->status_label.'. Total '.$pesananAktif->total_text.'.';
        @endphp

        <section class="mt-10 rounded-[20px] border border-line bg-white p-[32px]">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <p class="text-[15px] font-semibold text-brand">
                    PESANAN {{ $halaman }} DARI {{ $jumlahPesanan }}
                </p>

                <span class="rounded-[21px] bg-amber-soft px-5 py-2 text-[14px] font-semibold text-[#94570d]">
                    {{ strtoupper($pesananAktif->status_label) }}
                </span>
            </div>

            <h2 class="mt-4 text-[30px] font-bold leading-tight text-ink">{{ $pesananAktif->product?->name }}</h2>

            <p class="mt-2 text-[17px] text-muted">
                {{ $pesananAktif->product?->seller?->name ?? 'Toko SuaraNiaga' }}
                &bull; {{ $pesananAktif->quantity }} produk
            </p>

            <div class="mt-4 flex justify-end">
                <div class="text-right">
                    <p class="text-[13px] font-semibold text-muted">TOTAL PESANAN</p>
                    <p class="text-[28px] font-bold text-ink">{{ $pesananAktif->total_text }}</p>
                </div>
            </div>

            <hr class="my-6 border-line">

            <p class="text-[15px] font-semibold text-ink">PERJALANAN PESANAN</p>

            <ol class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($langkah as $nomor => $tahap)
                    @php
                        $nomor = $nomor + 1;
                        $selesai = $nomor < $posisiStatus + 1;
                        $sekarang = $nomor === $posisiStatus + 1;
                        $ikon = $selesai
                            ? 'langkah-selesai.svg'
                            : ($sekarang ? 'langkah-sekarang.svg' : 'langkah-belum.svg');
                    @endphp
                    <li class="flex items-center gap-3">
                        <img src="{{ asset('images/aksesibilitas/'.$ikon) }}"
                             alt="{{ $selesai ? 'Sudah selesai' : ($sekarang ? 'Status saat ini' : 'Belum selesai') }}"
                             width="28"
                             height="28">
                        <span>
                            <span class="block text-[16px] font-semibold text-ink">{{ $tahap['judul'] }}</span>
                            <span class="block text-[13px] text-muted">{{ $tahap['sub'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>

            <p class="mt-6 text-[13px] text-muted">
                ID Pesanan: {{ $pesananAktif->nomor }}
                &bull;
                Tanggal: {{ $pesananAktif->created_at?->format('j F Y') }}
            </p>
        </section>

        <div class="mt-8 flex flex-col gap-[18px] lg:flex-row lg:items-start lg:justify-between">
            <div class="flex flex-col gap-[18px] lg:flex-row lg:items-start">
                @if ($sebelumnya)
                    <div>
                        <x-aks.tombol varian="b" :href="$sebelumnya">Pesanan sebelumnya</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Tab / Shift+Tab = sebelumnya</p>
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
                        <x-aks.tombol varian="b" :href="$berikutnya">Pesanan berikutnya</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Tab / Shift+Tab = berikutnya</p>
                    </div>
                @endif
            </div>

            <div>
                <form method="POST" action="{{ route('pesanan.terima', $pesananAktif) }}" class="contents">
                    @csrf
                    @method('PATCH')
                    <x-aks.tombol varian="b" warna="sukses" type="submit">Konfirmasi diterima</x-aks.tombol>
                </form>
                <p class="mt-2 text-[12px] text-muted">Enter = konfirmasi diterima</p>
            </div>
        </div>
    @endif
@endsection
