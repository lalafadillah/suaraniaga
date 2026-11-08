@extends('layouts.app')

@section('content')
    @php
        $kelasInput = 'mt-2 h-[54px] w-full rounded-[9px] border bg-surface px-4 text-base text-body placeholder:text-muted focus:border-brand';
        $langkah = [
            ['key' => 'nama', 'name' => 'name', 'label' => 'Nama produk', 'placeholder' => 'Contoh: Beras Setra Ramos 5 kilogram', 'tipe' => 'text'],
            ['key' => 'harga', 'name' => 'price', 'label' => 'Harga', 'placeholder' => 'Contoh: 72000', 'tipe' => 'text'],
            ['key' => 'stok', 'name' => 'stock', 'label' => 'Stok', 'placeholder' => 'Contoh: 24', 'tipe' => 'text'],
            ['key' => 'deskripsi', 'name' => 'audio_description', 'label' => 'Deskripsi audio', 'placeholder' => 'Contoh: Beras pulen dengan tekstur lembut', 'tipe' => 'text'],
        ];
    @endphp

    <form method="POST"
          action="{{ route('penjual.produk.simpan') }}"
          data-form-siap
          x-data="{
              langkah: 1,
              total: 4,
              fields: {
                  nama: @js(old('name', '')),
                  harga: @js(old('price', '')),
                  stok: @js(old('stock', '')),
                  deskripsi: @js(old('audio_description', '')),
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
              normalisasiHarga(nilai) {
                  const teks = String(nilai).toLowerCase().trim();
                  const cocok = teks.match(/^(?:rp\.?\s*)?(\d{1,3}(?:[.,\s]\d{3})*|\d+)\s*(ribu|juta)?$/);

                  if (!cocok) {
                      return nilai;
                  }

                  let angka = parseInt(cocok[1].replace(/[.,\s]/g, ''), 10);

                  if (cocok[2] === 'ribu') {
                      angka = angka * 1000;
                  }

                  if (cocok[2] === 'juta') {
                      angka = angka * 1000000;
                  }

                  return String(angka);
              },
              normalisasiStok(nilai) {
                  const teks = String(nilai).trim();

                  return /^\d+$/.test(teks) ? String(parseInt(teks, 10)) : nilai;
              },
              setStepValue(value) {
                  const key = this.stepKey;
                  let hasil = value;

                  if (key === 'harga') {
                      hasil = this.normalisasiHarga(value);
                  }

                  if (key === 'stok') {
                      hasil = this.normalisasiStok(value);
                  }

                  this.fields[key] = hasil;
                  this.$store.announcer.say(this.stepLabel + ' terisi: ' + hasil);
              },
              next() {
                  if (! String(this.stepValue).trim()) {
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
        @csrf

        <x-page-heading
            title="Tambah produk dengan suara"
            description="Sebutkan informasi satu per satu. Periksa hasil transkripsi sebelum menyimpan."
        />

        @if ($errors->any())
            <div id="ringkasan-galat"
                 role="alert"
                 tabindex="-1"
                 class="mb-6 rounded-card border border-line bg-surface px-5 py-4">
                <h2 class="text-[17px] font-semibold text-ink">Ada kesalahan</h2>
                <ul class="mt-2 list-disc pl-6 text-[15px] text-body">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

                <div class="mt-7 space-y-6">
                    @foreach ($langkah as $index => $field)
                        <div x-show="langkah === {{ $index + 1 }}">
                            <label for="{{ $field['name'] }}" class="block text-[15px] font-semibold text-ink">
                                {{ $field['label'] }}
                                <span aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                            </label>

                            <input id="{{ $field['name'] }}"
                                   name="{{ $field['name'] }}"
                                   type="text"
                                   @if ($field['name'] === 'price' || $field['name'] === 'stock') inputmode="numeric" @endif
                                   x-model="fields.{{ $field['key'] }}"
                                   placeholder="{{ $field['placeholder'] }}"
                                   @if ($errors->has($field['name'])) aria-invalid="true" @endif
                                   @if ($errors->has($field['name'])) aria-describedby="{{ $field['name'] }}-galat" @endif
                                   class="{{ $kelasInput }} @if ($errors->has($field['name'])) border-danger @else border-line @endif">

                            @if ($errors->has($field['name']))
                                <p id="{{ $field['name'] }}-galat" class="mt-1.5 text-[13px] font-medium text-danger">
                                    {{ $errors->first($field['name']) }}
                                </p>
                            @endif

                            <p class="mt-2 text-[13px] text-muted-soft">
                                Teks hasil dikte dapat diedit sebelum disimpan.
                            </p>
                        </div>
                    @endforeach
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

                    <x-btn variant="outline"
                           x-on:click="next()"
                           x-show="langkah < total">
                        Lanjut ke <span x-text="labels[['nama','harga','stok','deskripsi'][langkah]]"></span>
                    </x-btn>
                </div>

                <div class="mt-6 border-t border-line pt-6">
                    <x-btn type="submit">Simpan produk</x-btn>
                    <p class="mt-2 text-[13px] text-muted-soft">
                        Semua kolom bisa diisi dengan mengetik. Suara hanya speeding.
                    </p>
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
    </form>
@endsection
