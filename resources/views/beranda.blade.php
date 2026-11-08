@extends('layouts.app')

@section('content')
    {{-- Tampilan mobile: mengikuti frame 15 Figma (390 x 844) --}}
    <div class="lg:hidden">
        <h1 class="text-[24px] font-bold text-ink">Selamat datang</h1>
        <p class="mt-2 text-[15px] text-muted">Belanja mandiri dengan suara.</p>

        <div class="mt-6 flex flex-col gap-3">
            <x-btn href="{{ route('cari') }}" size="mobile-lg" class="w-full">Mulai cari produk</x-btn>
            <x-btn href="{{ route('pesanan') }}" variant="outline" size="mobile" class="w-full">Pesanan saya</x-btn>
            <x-btn href="{{ route('cari') }}" variant="outline" size="mobile" class="w-full">Jelajahi kategori</x-btn>
        </div>

        <hr class="my-6 border-line">

        <h2 class="text-[18px] font-semibold text-ink">Navigasi sederhana</h2>
        <p class="mt-2 text-[14px] text-muted">Beranda • Cari • Keranjang • Profil</p>

        <div class="mt-6 rounded-[12px] bg-brand-soft p-5">
            <h2 class="text-[16px] font-semibold text-brand">Asisten suara</h2>
            <p class="mt-1.5 text-[13px] text-muted">Tekan tombol mikrofon saat ingin berbicara.</p>
        </div>
    </div>

    {{-- Tampilan desktop: mengikuti frame 01 Figma (1440 x 920) --}}
    <div class="hidden lg:block">
        <x-page-heading
            title="Selamat datang di SuaraNiaga"
            description="Belanja mandiri dengan suara. Semua menu bisa digunakan memakai keyboard atau pembaca layar."
        />

        {{-- Panel pencarian utama: satu tindakan utama, sesuai brief desain --}}
        <section class="rounded-panel border border-line bg-surface p-7">
            <h2 class="text-[24px] font-semibold text-ink">Apa yang ingin kamu cari hari ini?</h2>
            <p class="mt-2 max-w-[700px] text-base text-muted">
                Ucapkan nama barang, atau pilih tombol pencarian di bawah.
            </p>

            <div class="mt-6 flex flex-wrap gap-4">
                <x-btn href="{{ route('cari') }}" size="lg">Mulai cari produk</x-btn>
            </div>
        </section>

        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <section class="rounded-card border border-line bg-surface p-6">
                <h2 class="text-[18px] font-semibold text-ink">Cari berdasarkan suara</h2>
                <p class="mt-2 text-[15px] text-muted">
                    Tekan tombol mikrofon, lalu sebutkan nama barang yang kamu butuhkan.
                </p>

                <div class="mt-5" x-data="{ query: '' }"
                     x-on:voice-transcript.window="query = $event.detail.value; $store.announcer.say('Hasil suara: ' + query)">

                    <x-voice-mic label="Mulai pencarian suara"
                                 hint="Jika mikrofon tidak didukung, ketik di bawah."
                                 @voice-transcript="query = $event.detail.value" />

                    <form action="{{ route('cari') }}" method="GET" class="mt-4 flex gap-3">
                        <label for="kategori" class="sr-only">Kata kunci produk</label>
                        <input id="kategori"
                               name="q"
                               type="search"
                               x-model="query"
                               placeholder="Contoh: beras 5 kilogram"
                               class="h-[54px] w-full rounded-[9px] border border-line bg-surface px-4 text-base text-body placeholder:text-muted focus:border-brand">
                        <x-btn type="submit">Cari</x-btn>
                    </form>
                </div>
            </section>

            <section class="rounded-card border border-line bg-surface p-6">
                <h2 class="text-[18px] font-semibold text-ink">Lanjutkan aktivitas</h2>
                <p class="mt-2 text-[15px] text-muted">
                    Lihat pesanan terakhir dan status pengiriman tanpa harus mencari menu.
                </p>

                <div class="mt-5">
                    <x-btn href="{{ route('pesanan') }}" variant="outline">Pesanan saya</x-btn>
                </div>
            </section>
        </div>

        <h2 class="mt-10 text-[20px] font-semibold text-ink">Akses cepat</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <x-btn href="{{ route('cari') }}" variant="outline">Jelajahi kategori</x-btn>
            <x-btn href="{{ route('keranjang') }}" variant="outline">Buka keranjang</x-btn>
            <x-btn href="{{ route('pengaturan') }}" variant="outline">Pengaturan aksesibilitas</x-btn>
        </div>

        <p class="mt-10 max-w-[1020px] text-sm text-muted">
            Catatan: NVDA / Narrator akan membacakan elemen saat fokus berpindah. SuaraNiaga tidak menggandakan suara pembaca layar.
        </p>
    </div>
@endsection
