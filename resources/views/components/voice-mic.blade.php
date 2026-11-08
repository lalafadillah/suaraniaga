@props([
    'label' => 'Mulai merekam',
    'stopLabel' => 'Hentikan rekaman',
    'hint' => null,
    'size' => 'md', // md | lg
])

{{--
    Memancarkan event `voice-transcript` dengan teks final agar parent bisa
    memakainya. Fallback ke input teks selalu disediakan oleh halaman.
--}}
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-4']) }}
     x-data="{
        recording: false,
        start() {
            this.recording = true;
            $store.suara.start((value) => {
                this.recording = false;
                this.$dispatch('voice-transcript', { value });
            });
        },        stop() {
            this.recording = false;
            $store.suara.stop();
        },
     }">

    <x-btn variant="soft"
           size="{{ $size === 'lg' ? 'lg' : 'md' }}"
           x-bind:class="recording ? '!border-brand !bg-brand-soft' : ''"
           x-on:click="recording ? stop() : start()"
           x-bind:aria-pressed="recording">
        <span x-show="!recording">{{ $label }}</span>
        <span x-show="recording" x-cloak>{{ $stopLabel }}</span>
    </x-btn>

    <x-status-pill tone="success" x-show="$store.suara.state === 'listening'">Sedang mendengarkan</x-status-pill>
    <x-status-pill tone="info" x-show="$store.suara.state === 'processing'" x-cloak>Memproses suara</x-status-pill>

    @if ($hint)
        <p class="text-[13px] text-muted-soft">{{ $hint }}</p>
    @endif

    <p class="w-full text-[13px] text-muted-soft" x-show="$store.suara.error" x-cloak x-text="$store.suara.error" role="alert"></p>
</div>
