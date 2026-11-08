@props([
    'heading' => 'Asisten suara SuaraNiaga',
    'description' => 'Satu status aktif pada satu waktu. Teks status juga diumumkan melalui live region.',
])

{{--
    Panel asisten suara dengan empat status yang saling menggantikan.
    Setiap status diumumkan lewat live region agar pembaca layar/users
    low-vision tahu apa yang sedang terjadi tanpa melihat layar.
--}}
<section {{ $attributes->merge(['class' => 'rounded-panel border border-line bg-surface p-7']) }}
         aria-labelledby="asisten-suara-judul">

    <h2 id="asisten-suara-judul" class="text-[22px] font-semibold text-ink">{{ $heading }}</h2>
    <p class="mt-2 max-w-[720px] text-[15px] text-muted">{{ $description }}</p>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @foreach ([
            ['state' => 'idle', 'title' => 'Siap mendengarkan', 'body' => 'Tekan tombol mikrofon untuk mulai.', 'action' => 'Mulai berbicara', 'tone' => 'success'],
            ['state' => 'listening', 'title' => 'Sedang mendengarkan', 'body' => 'Silakan sebutkan produk yang dicari.', 'action' => 'Hentikan rekaman', 'tone' => 'info'],
            ['state' => 'processing', 'title' => 'Memproses suara', 'body' => 'Sedang memahami permintaanmu.', 'action' => 'Tunggu sebentar', 'tone' => 'info'],
            ['state' => 'speaking', 'title' => 'Sedang membacakan', 'body' => 'Beras Setra Ramos 5 kg, harga Rp 72.000.', 'action' => 'Jeda suara', 'tone' => 'info'],
        ] as $card)
            <div class="rounded-card border border-line bg-surface p-5"
                 x-show="$store.suara.state === '{{ $card['state'] }}'"
                 x-cloak>

                <h3 class="text-[17px] font-semibold text-ink">{{ $card['title'] }}</h3>
                <p class="mt-1.5 text-[15px] text-muted">{{ $card['body'] }}</p>

                @if ($card['state'] === 'speaking')
                    <x-btn variant="soft" size="sm" class="mt-4" x-on:click="$store.suara.stopSpeaking()">{{ $card['action'] }}</x-btn>
                @elseif ($card['state'] === 'listening')
                    <x-btn variant="soft" size="sm" class="mt-4" x-on:click="$store.suara.stop()">{{ $card['action'] }}</x-btn>
                @elseif ($card['state'] === 'processing')
                    <x-btn variant="soft" size="sm" class="mt-4" disabled>{{ $card['action'] }}</x-btn>
                @else
                    <x-btn variant="soft" size="sm" class="mt-4" x-on:click="$store.suara.start(() => {})">{{ $card['action'] }}</x-btn>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-[10px] bg-brand px-5 py-4">
        <p class="text-[15px] font-semibold text-white">
            FOKUS: <span class="font-normal" x-text="$store.announcer.message || 'Tombol Cari Produk — tekan Enter untuk membuka pencarian'"></span>
        </p>
    </div>

    <p class="mt-5 text-[14px] text-muted">
        Fallback: jika mikrofon tidak didukung atau izin ditolak, sediakan kolom input teks biasa.
    </p>
</section>
