@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Pengaturan aksesibilitas"
        description="Atur pengalaman sesuai kebutuhan. Pengaturan tidak mengubah struktur navigasi."
    />

    <div class="space-y-[14px]">
        @foreach ([
            ['key' => 'textScale', 'judul' => 'Ukuran teks', 'ringkas' => 'Normal / Besar / Sangat besar', 'opsi' => [
                ['value' => 'normal', 'label' => 'Normal'],
                ['value' => 'besar', 'label' => 'Besar'],
                ['value' => 'sangat-besar', 'label' => 'Sangat besar'],
            ]],
            ['key' => 'contrast', 'judul' => 'Kontras tampilan', 'ringkas' => 'Standar / Kontras tinggi', 'opsi' => [
                ['value' => 'standar', 'label' => 'Standar'],
                ['value' => 'tinggi', 'label' => 'Kontras tinggi'],
            ]],
            ['key' => 'screenReader', 'judul' => 'Pembaca layar', 'ringkas' => 'Gunakan NVDA / Narrator / VoiceOver', 'opsi' => [
                ['value' => 'nvda', 'label' => 'Gunakan NVDA'],
                ['value' => 'narrator', 'label' => 'Narrator'],
                ['value' => 'voiceover', 'label' => 'VoiceOver'],
            ]],
            ['key' => 'voiceAssistant', 'judul' => 'Asisten suara', 'ringkas' => 'Aktif / Nonaktif', 'opsi' => [
                ['value' => 'aktif', 'label' => 'Aktif'],
                ['value' => 'nonaktif', 'label' => 'Nonaktif'],
            ]],
            ['key' => 'voiceNavigation', 'judul' => 'Pembacaan navigasi', 'ringkas' => 'Nama dan jenis elemen dibacakan saat pointer melewati atau fokus berpindah', 'opsi' => [
                ['value' => 'auto', 'label' => 'Ikuti mode aksesibilitas'],
                ['value' => 'aktif', 'label' => 'Aktif'],
                ['value' => 'nonaktif', 'label' => 'Nonaktif'],
            ]],
            ['key' => 'speechRate', 'judul' => 'Kecepatan suara', 'ringkas' => 'Lambat / Normal / Cepat', 'opsi' => [
                ['value' => 'lambat', 'label' => 'Lambat'],
                ['value' => 'normal', 'label' => 'Normal'],
                ['value' => 'cepat', 'label' => 'Cepat'],
            ]],
            ['key' => 'motion', 'judul' => 'Animasi', 'ringkas' => 'Kurangi animasi', 'opsi' => [
                ['value' => 'normal', 'label' => 'Normal'],
                ['value' => 'kurangi', 'label' => 'Kurangi animasi'],
            ]],
        ] as $baris)
            @php $grup = 'grup-' . $baris['key']; @endphp

            <div class="rounded-card border border-line bg-surface" x-data="{ buka: false }">
                <div class="flex flex-wrap items-center gap-4 px-6 py-5">
                    <h2 class="w-full text-base font-semibold text-ink sm:w-[320px]">{{ $baris['judul'] }}</h2>

                    <p class="flex-1 text-[15px] text-muted">{{ $baris['ringkas'] }}</p>

                    <button type="button"
                            x-on:click="buka = !buka"
                            x-bind:aria-expanded="buka"
                            x-bind:aria-controls="'{{ $grup }}'"
                            class="text-[13px] font-semibold text-brand underline-offset-4 hover:underline">
                        Ubah
                    </button>
                </div>

                <div id="{{ $grup }}" x-show="buka" x-cloak
                     class="border-t border-line px-6 py-5">
                    <div class="flex flex-wrap gap-2" role="group" aria-label="{{ $baris['judul'] }}">
                        @foreach ($baris['opsi'] as $opsi)
                            <button type="button"
                                    x-on:click="$store.prefs.set(@js($baris['key']), @js($opsi['value'])); $store.announcer.say(@js($baris['judul'] . ' diubah ke ' . $opsi['label'])); buka = false"
                                    x-bind:aria-pressed="$store.prefs.values[@js($baris['key'])] === @js($opsi['value'])"
                                    x-bind:class="$store.prefs.values[@js($baris['key'])] === @js($opsi['value'])
                                        ? 'rounded-full bg-brand-soft px-4 py-2 text-[15px] font-semibold text-brand'
                                        : 'rounded-full border border-line px-4 py-2 text-[15px] font-medium text-muted hover:bg-brand-soft'">
                                {{ $opsi['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <p class="mt-8 max-w-[1000px] text-sm text-muted">
        Catatan: NVDA / Narrator / VoiceOver tetap menjadi pembaca layar utama. Asisten Suara SuaraNiaga adalah fitur tambahan, bukan pengganti screen reader.
        Pembacaan navigasi mengikuti mode aksesibilitas; pilih Nonaktif di baris <strong>Pembacaan navigasi</strong> bila ingin membungkamnya.
    </p>

    <div class="mt-10">
        <x-voice-panel />
    </div>
@endsection
