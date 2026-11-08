@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    <h1 class="text-[42px] font-bold leading-tight text-ink">Bantuan aksesibilitas</h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        Halaman ini menjelaskan cara memakai SuaraNiaga dengan pembaca layar dan keyboard.
    </p>

    <section class="mt-10 space-y-8">
        <div>
            <h2 class="text-[28px] font-bold text-ink">Cara berpindah halaman</h2>
            <p class="mt-2 text-[22px] leading-relaxed text-body">
                Tekan Tab untuk pindah ke tombol berikutnya, Shift + Tab untuk kembali ke tombol sebelumnya,
                dan Enter untuk memilih. Tombol Lompat ke konten utama ada di bagian atas setiap halaman.
            </p>
        </div>

        <div>
            <h2 class="text-[28px] font-bold text-ink">Mencari dengan suara</h2>
            <p class="mt-2 text-[22px] leading-relaxed text-body">
                Pada halaman pencarian, tekan tombol mikrofon lalu ucapkan nama produk.
                Jika mikrofon tidak tersedia, ketik nama produk di kolom pencarian. Keduanya memberi hasil yang sama.
            </p>
        </div>

        <div>
            <h2 class="text-[28px] font-bold text-ink">Mendengarkan deskripsi</h2>
            <p class="mt-2 text-[22px] leading-relaxed text-body">
                Tombol Dengarkan deskripsi membacakan isi kartu produk atau pesanan.
                Suara hanya diputar setelah Anda menekan tombolnya.
            </p>
        </div>

        <div>
            <h2 class="text-[28px] font-bold text-ink">Satu langkah satu halaman</h2>
            <p class="mt-2 text-[22px] leading-relaxed text-body">
                Mode aksesibilitas menampilkan satu isian per halaman, jadi Anda tidak perlu melewati banyak
                kolom sekaligus. Isi kolom yang tersedia, lalu tekan Lanjutkan.
            </p>
        </div>

        <div>
            <h2 class="text-[28px] font-bold text-ink">Pembacaan navigasi</h2>
            <p class="mt-2 text-[22px] leading-relaxed text-body">
                Saat pointer melewati tombol atau tautan, dan saat fokus berpindah dengan Tab,
                nama dan jenis elemen itu dibacakan otomatis dalam Bahasa Indonesia.
                Pilih Matikan bila ingin membungkamnya. Pilihan ini disimpan di peramban ini.
            </p>

            <div role="group" aria-label="Pembacaan navigasi" class="mt-4 flex flex-col gap-[18px]">
                @foreach ([
                    ['value' => 'auto', 'label' => 'Ikuti mode aksesibilitas'],
                    ['value' => 'aktif', 'label' => 'Aktifkan'],
                    ['value' => 'nonaktif', 'label' => 'Matikan'],
                ] as $opsi)
                    <x-aks.tombol varian="b"
                                 warna="sekunder"
                                 type="button"
                                 x-on:click="$store.prefs.set('voiceNavigation', '{{ $opsi['value'] }}'); $store.announcer.say('Pembacaan navigasi {{ $opsi['label'] }}');"
                                 x-bind:aria-pressed="$store.prefs.values.voiceNavigation === '{{ $opsi['value'] }}'">
                        {{ $opsi['label'] }}
                    </x-aks.tombol>
                @endforeach
            </div>
        </div>
    </section>

    @php
        $pengguna = auth()->user();
        $rumah = $pengguna?->isSeller() ? route('penjual.dashboard') : route('beranda');
        $labelRumah = $pengguna?->isSeller() ? 'Kembali ke ringkasan toko' : 'Kembali ke beranda';
    @endphp

    <div class="mt-10 flex flex-col gap-[23px]">
        <x-aks.tombol warna="biru" :href="$rumah">{{ $labelRumah }}</x-aks.tombol>
    </div>
@endsection
