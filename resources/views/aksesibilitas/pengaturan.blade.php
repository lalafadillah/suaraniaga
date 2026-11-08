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
    </section>

    <div class="mt-10 flex flex-col gap-[23px]">
        <x-aks.tombol warna="biru" href="{{ route('beranda') }}">Kembali ke beranda</x-aks.tombol>
    </div>
@endsection
