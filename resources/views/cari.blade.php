@extends('layouts.app')

@section('content')
    <div x-data="{ query: '{{ request('q') }}' }"
         x-on:voice-transcript.window="query = $event.detail.value; $store.announcer.say('Mendengar: ' + query)">

        {{-- Tampilan mobile: mengikuti frame 16 Figma (390 x 844) --}}
        <div class="lg:hidden">
            <label for="kata-kunci-mobile" class="sr-only">Sebutkan nama produk</label>
            <input id="kata-kunci-mobile"
                   name="q"
                   type="search"
                   x-model="query"
                   placeholder="Sebutkan nama produk"
                   class="mt-4 h-[58px] w-full rounded-[10px] border border-line bg-canvas px-4 text-[15px] text-body placeholder:text-muted focus:border-brand">

            <button type="button"
                    class="mt-3 flex h-[62px] w-full items-center justify-center rounded-[10px] bg-amber-soft text-[16px] font-semibold text-ink"
                    x-on:click="$store.suara.start((value) => { query = value; $store.announcer.say('Mendengar: ' + value); })">
                Mulai pencarian suara
            </button>

            <p class="mt-3 text-[13px] text-muted-soft" x-show="$store.suara.error" x-cloak x-text="$store.suara.error" role="alert"></p>

            <div class="mt-6 inline-flex h-[30px] items-center rounded-[15px] bg-success-soft px-[10px] text-[12px] font-semibold text-success">
                Hasil ditemukan
            </div>

            @foreach ($produk as $item)
                <article class="mt-5">
                    <h2 class="text-[18px] font-semibold text-ink">{{ $item['nama'] }}</h2>
                    <p class="mt-1 text-[18px] font-bold text-brand">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                    <p class="mt-2 text-[14px] text-muted">
                        Deskripsi audio tersedia. Tekan dengarkan untuk mengetahui detail produk.
                    </p>

                    <div class="mt-4 flex flex-col gap-3">
                        <x-btn variant="outline" class="w-full"
                               x-on:click="$store.suara.speak(@js($item['audio']))"
                               aria-label="Dengarkan deskripsi {{ $item['nama'] }}">
                            Dengarkan deskripsi
                        </x-btn>
                        <x-btn href="{{ route('produk.detail', $item['id']) }}" size="mobile" class="w-full">
                            Lihat dan pesan {{ $item['nama'] }}
                        </x-btn>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Tampilan desktop: mengikuti frame 02 Figma (1440 x 920) --}}
        <div class="hidden lg:block">
            <x-page-heading
                title="Cari produk"
                description="Ketik kata kunci atau gunakan pencarian suara. Hasil dibacakan secara berurutan."
            />

            <section class="rounded-card border border-line bg-surface p-6">
                <label for="kata-kunci" class="block text-[13px] font-semibold text-ink">Kata kunci produk</label>

                <div class="mt-3 flex flex-wrap gap-3">
                    <input id="kata-kunci"
                           name="q"
                           type="search"
                           x-model="query"
                           placeholder="Contoh: beras 5 kilogram"
                           class="h-[54px] min-w-0 flex-1 rounded-[9px] border border-line bg-surface px-4 text-[18px] text-body placeholder:text-muted focus:border-brand">

                    <x-btn type="submit" form="form-cari">Cari</x-btn>

                    <x-btn variant="soft" form="form-cari" type="submit"
                           x-on:click.prevent="$store.suara.start((value) => { query = value; $store.announcer.say('Mendengar: ' + value); $refs.form.requestSubmit(); })">
                        Gunakan suara
                    </x-btn>
                </div>

                <form id="form-cari" action="{{ route('cari') }}" method="GET" class="sr-only">
                    <input type="hidden" name="q" :value="query">
                </form>

                <p class="mt-4 text-[13px] text-muted-soft" x-show="$store.suara.error" x-cloak x-text="$store.suara.error" role="alert"></p>
            </section>

            <h2 class="mt-8 text-[22px] font-semibold text-ink">
                Hasil pencarian: <span x-text="query || 'Beras'">{{ request('q', 'Beras') }}</span>
            </h2>

            <div class="mt-3 flex items-center gap-3">
                <x-status-pill tone="success">{{ count($produk) }} produk ditemukan</x-status-pill>
            </div>

            {{-- Grid produk: urutan DOM mengikuti urutan baca pembaca layar --}}
            <ul class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($produk as $item)
                    <li class="flex flex-col rounded-card border border-line bg-surface p-5">
                        <div class="flex h-28 items-center justify-center rounded-[10px] bg-brand-soft px-4">
                            <p class="text-center text-[13px] font-medium text-brand">
                                Deskripsi produk tersedia dalam audio
                            </p>
                        </div>

                        <h3 class="mt-4 text-[18px] font-semibold text-ink">
                            <a href="{{ route('produk.detail', $item['id']) }}" class="hover:underline">{{ $item['nama'] }}</a>
                        </h3>

                        <p class="mt-1 text-sm text-muted">{{ $item['ringkas'] }}</p>

                        <p class="mt-3 text-[18px] font-bold text-brand">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>

                        <div class="mt-auto pt-4">
                            <x-btn variant="ghost" size="sm"
                                   x-on:click="$store.suara.speak(@js($item['audio']))"
                                   aria-label="Dengarkan audio deskripsi {{ $item['nama'] }}">
                                Dengar audio
                            </x-btn>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
