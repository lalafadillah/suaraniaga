@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    <h1 class="text-[40px] font-bold leading-tight text-ink">Cari produk dengan suara</h1>
    <p class="mt-3 text-[22px] leading-snug text-muted">
        Tekan tombol mikrofon, lalu ucapkan nama produk yang ingin dicari. Setelah diam dua detik,
        pencarian berjalan sendiri.
    </p>

    <div class="mt-10 flex flex-col items-center"
         x-data="{ nilai: '' }">
        <button type="button"
                aria-label="Mulai dengarkan pencarian suara"
                class="flex h-[400px] w-[400px] max-w-full items-center justify-center rounded-[200px] bg-brand-soft transition hover:bg-line-strong"
                :disabled="$store.suara.state === 'listening' || $store.suara.state === 'menunggu'"
                x-on:click="$store.suara.dengarSampaiDiam({
                    label: 'Pencarian',
                    bicara: false,
                    onHasil: (hasil) => {
                        nilai = hasil;
                        $refs.kunci.value = hasil;
                        $store.suara.speak('Mendengar: ' + hasil).then(() => $refs.form.submit());
                    }
                })">
            <img src="{{ asset('images/aksesibilitas/mikrofon.svg') }}"
                 alt=""
                 aria-hidden="true"
                 width="132"
                 height="132">
        </button>

        <p class="mt-6 text-[28px] font-bold text-success"
           x-show="$store.suara.state === 'listening'"
           x-cloak>MIKROFON SEDANG MENYALA</p>

        <p class="mt-6 text-[24px] text-ink"
           x-show="$store.suara.state === 'listening'"
           x-cloak>Silakan ucapkan nama produk. Contoh: &ldquo;minyak goreng dua liter&rdquo;. Diam dua detik setelah selesai bicara.</p>

        <p class="mt-4 text-[20px] font-medium text-muted" role="status" aria-live="polite">
            <span x-show="$store.suara.state === 'menunggu'">Menunggu pidato yang sedang dibacakan selesai.</span>
            <span x-show="$store.suara.state === 'listening'">Mendengarkan. Bicara sekarang, lalu diam selama dua detik.</span>
        </p>

        <p class="mt-2 text-[20px] font-medium text-danger" role="alert" x-show="$store.suara.error" x-cloak x-text="$store.suara.error"></p>

        <div class="mt-6 w-full max-w-[560px]">
            <x-aks.tombol type="button" class="w-full" x-on:click="$store.suara.stop()">
                Selesai berbicara
            </x-aks.tombol>
            <p class="mt-2 text-center text-[12px] text-muted">Enter = hentikan mendengarkan dan pakai hasil yang terdengar</p>
        </div>

        <form method="GET" action="{{ route('cari') }}" x-ref="form" class="mt-10 w-full max-w-[560px]">
            <x-aks.field name="q"
                         label="Atau ketik nama produk"
                         type="search"
                         placeholder="Ketik nama produk"
                         hint="Alternatif ketik untuk pencarian suara."
                         x-ref="kunci" />
            <div class="mt-6">
                <x-aks.tombol type="submit" class="w-full">Cari produk</x-aks.tombol>
            </div>
        </form>
    </div>

    <p class="mt-8 text-[18px] font-medium text-muted">
        Ketika suara berhenti, sistem memproses ucapan dan menampilkan hasil produk secara otomatis.
    </p>
@endsection
