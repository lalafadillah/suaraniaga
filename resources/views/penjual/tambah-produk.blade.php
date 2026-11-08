@extends('layouts.app')

@section('content')
    <div x-data="{
        langkah: 1,
        total: 4,
        fields: {
            nama: 'Beras Setra Ramos 5 kilogram',
            harga: '',
            stok: '',
            deskripsi: '',
        },
        labels: {
            nama: 'Nama produk',
            harga: 'Harga',
            stok: 'Stok',
            deskripsi: 'Deskripsi audio',
        },
        instructions: {
            nama: 'Tekan tombol, lalu ucapkan nama produk.',
            harga: 'Tekan tombol, lalu ucapkan harga, misalnya tujuh puluh dua ribu.',
            stok: 'Tekan tombol, lalu ucapkan jumlah stok.',
            deskripsi: 'Tekan tombol, lalu ucapkan deskripsi produk dengan lengkap.',
        },
        placeholders: {
            nama: 'Contoh: Beras Setra Ramos 5 kilogram',
            harga: 'Contoh: 72000',
            stok: 'Contoh: 24',
            deskripsi: 'Contoh: Beras pulen dengan tekstur lembut',
        },
        get stepKey() {
            return ['nama', 'harga', 'stok', 'deskripsi'][this.langkah - 1];
        },
        get stepLabel() {
            return this.labels[this.stepKey];
        },
        get stepInstruction() {
            return this.instructions[this.stepKey];
        },
        get stepPlaceholder() {
            return this.placeholders[this.stepKey];
        },
        get stepValue() {
            return this.fields[this.stepKey];
        },
        setStepValue(value) {
            this.fields[this.stepKey] = value;
            this.$store.announcer.say(this.stepLabel + ' terisi: ' + value);
        },
        next() {
            if (! this.stepValue.trim()) {
                this.$store.announcer.say(this.stepLabel + ' masih kosong');
                return;
            }

            if (this.langkah < this.total) {
                this.langkah += 1;
                this.$store.announcer.say('Langkah ' + this.langkah + ' dari ' + this.total + ', ' + this.stepLabel);
            }
        },
        back() {
            if (this.langkah > 1) {
                this.langkah -= 1;
                this.$store.announcer.say('Kembali ke langkah ' + this.langkah + ', ' + this.stepLabel);
            }
        },
        get preview() {
            return {
                'Nama': this.fields.nama || 'belum diisi',
                'Harga': this.fields.harga || 'belum diisi',
                'Stok': this.fields.stok || 'belum diisi',
                'Deskripsi audio': this.fields.deskripsi || 'belum direkam',
            };
        },
    }"
    x-on:voice-transcript.window="setStepValue($event.detail.value)">

        <x-page-heading
            title="Tambah produk dengan suara"
            description="Sebutkan informasi satu per satu. Periksa hasil transkripsi sebelum menyimpan."
        />

        <div class="grid gap-6 lg:grid-cols-[680px_360px]">
            <section class="rounded-panel border border-line bg-surface p-7" aria-labelledby="langkah-judul">
                <h2 id="langkah-judul" class="text-[20px] font-semibold text-ink">
                    Langkah <span x-text="langkah"></span> dari <span x-text="total"></span> — <span x-text="stepLabel"></span>
                </h2>

                <p class="mt-3 text-[15px] text-muted" x-text="stepInstruction"></p>

                <div class="mt-6 flex flex-wrap items-center gap-4">
                    <x-voice-mic label="Mulai merekam" stop-label="Hentikan rekaman" />

                    <x-status-pill tone="success" x-show="$store.suara.state === 'idle'">Siap mendengarkan</x-status-pill>
                </div>

                <div class="mt-7">
                    <label for="hasil-transkripsi" class="block text-sm font-semibold text-ink">Hasil transkripsi</label>

                    <input id="hasil-transkripsi"
                           type="text"
                           x-model="fields[stepKey]"
                           x-bind:placeholder="stepPlaceholder"
                           class="mt-2 h-[54px] w-full rounded-[9px] border border-line bg-surface px-4 text-base text-body placeholder:text-muted focus:border-brand">

                    <p class="mt-2 text-[13px] text-muted-soft">
                        Teks hasil dikte dapat diedit sebelum disimpan.
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-btn variant="outline"
                           x-on:click="back()"
                           x-bind:disabled="langkah === 1">
                        Kembali
                    </x-btn>

                    <x-btn variant="outline"
                           x-on:click="$store.suara.start((value) => setStepValue(value))">
                        Ulangi rekaman
                    </x-btn>

                    <x-btn x-on:click="next()">
                        <span x-show="langkah < total">Lanjut ke <span x-text="labels[['nama','harga','stok','deskripsi'][langkah]]"></span></span>
                        <span x-show="langkah === total" x-cloak>Simpan produk</span>
                    </x-btn>
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-card border border-line bg-surface p-6" aria-labelledby="preview-judul">
                    <h2 id="preview-judul" class="text-[18px] font-semibold text-ink">Preview data produk</h2>

                    <dl class="mt-4 space-y-2 text-[15px] text-muted">
                        <template x-for="(nilai, label) in preview" :key="label">
                            <div class="flex gap-2">
                                <dt class="font-medium text-ink" x-text="label + ':'"></dt>
                                <dd x-text="nilai"></dd>
                            </div>
                        </template>
                    </dl>
                </section>

                <section aria-labelledby="privasi-judul">
                    <h2 id="privasi-judul" class="text-[17px] font-semibold text-ink">Privasi & kontrol</h2>
                    <p class="mt-2 text-sm text-muted">
                        Rekaman hanya digunakan untuk mengisi formulir. Penjual dapat mengedit teks sebelum menyimpan.
                    </p>
                </section>
            </div>
        </div>

        <section class="mt-6 rounded-[10px] border border-line-strong bg-brand-soft p-5" aria-labelledby="wajib-judul">
            <h2 id="wajib-judul" class="text-[17px] font-semibold text-ink">Empat data wajib sebelum produk disimpan</h2>

            <p class="mt-2 text-[15px] font-semibold text-brand">
                1. Nama produk → 2. Harga → 3. Stok → 4. Deskripsi audio
            </p>

            <p class="mt-1.5 text-[13px] text-muted-soft">
                Periksa dan edit hasil dikte. Deskripsi audio wajib diisi; tersedia input teks sebagai alternatif.
            </p>
        </section>
    </div>
@endsection
