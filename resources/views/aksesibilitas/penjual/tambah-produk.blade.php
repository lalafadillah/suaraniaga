@extends('layouts.aksesibilitas', ['varian' => 'b'])

@section('content')
    @php
        $urutan = \App\Http\Controllers\SellerController::LANGKAH_PRODUK;
        $nomor = array_search($langkah, $urutan, true) + 1;
        $total = count($urutan);
        $sumber = $konfigurasi[$langkah];
        $terisi = $terisi ?? [];
        $namaField = [
            'nama' => 'name',
            'harga' => 'price',
            'stok' => 'stock',
            'deskripsi' => 'description',
        ][$langkah] ?? null;
        $nilai = $namaField ? ($terisi[$namaField] ?? '') : '';
        $simpan = $langkah === 'simpan';
        $berikutnya = $urutan[$nomor] ?? null;
    @endphp

    <h1 class="text-[42px] font-semibold leading-tight text-ink">{{ $sumber['judul'] }}</h1>
    <p class="mt-3 text-[18px] leading-snug text-muted">
        Ikuti instruksi suara untuk mengisi informasi produk. Periksa hasil pengenalan sebelum menyimpan.
    </p>

    <div class="mt-10" x-data="{ nilai: @js((string) $nilai) }">
        <section class="rounded-[16px] border border-line-strong bg-white p-[32px]">
            <p class="text-[15px] font-semibold text-brand">LANGKAH {{ $nomor }} DARI {{ $total }}</p>

            <h2 class="mt-3 text-[30px] font-semibold text-ink">{{ $sumber['judul'] }}</h2>

            @if ($simpan)
                <p class="mt-3 text-[20px] text-muted">
                    Nama: {{ $terisi['name'] ?? '-' }} &bull; Harga: {{ $terisi['price'] ?? '-' }}
                    &bull; Stok: {{ $terisi['stock'] ?? '-' }}
                </p>
                <p class="mt-1 text-[20px] leading-relaxed text-muted">
                    {{ $terisi['description'] ?? '-' }}
                </p>
            @else
                <p class="mt-3 text-[20px] text-muted" x-text="nilai !== '' ? 'Hasil suara: ' + nilai : 'Belum ada hasil'">
                    {{ $nilai !== '' ? 'Hasil suara: '.$nilai : 'Belum ada hasil' }}
                </p>
            @endif
        </section>

        <p class="mt-6 text-[20px] font-medium text-brand"
           role="status"
           aria-live="polite"
           x-text="$store.suara.state === 'menunggu'
               ? 'Menunggu pidato yang sedang dibacakan selesai.'
               : ($store.suara.state === 'listening'
                   ? 'Mendengarkan. Bicara sekarang, lalu diam selama dua detik.'
                   : '')"></p>

        <p class="mt-2 text-[20px] font-medium text-danger"
           x-show="$store.suara.error !== ''"
           x-text="$store.suara.error"></p>

        <x-aks.pesan class="mt-6" />

        <form method="POST"
              action="{{ $simpan ? route('penjual.produk.simpan') : route('penjual.produk.tambah.langkah', $langkah) }}"
              class="mt-6"
              data-form-siap>
            @csrf

            @if (! $simpan)
                <x-aks.field name="{{ $namaField }}"
                             label="{{ $sumber['label'] }}"
                             type="{{ $sumber['tipe'] }}"
                             :value="$nilai"
                             :placeholder="$sumber['placeholder']"
                             x-model="nilai"
                             required />
            @else
                @if (! empty($terisi['produk_id']))
                    <input type="hidden" name="produk_id" value="{{ $terisi['produk_id'] }}">
                @endif
                <input type="hidden" name="name" value="{{ $terisi['name'] ?? '' }}">
                <input type="hidden" name="price" value="{{ $terisi['price'] ?? '' }}">
                <input type="hidden" name="stock" value="{{ $terisi['stock'] ?? 0 }}">
                <input type="hidden" name="description" value="{{ $terisi['description'] ?? '' }}">
                <input type="hidden" name="audio_description" value="{{ $terisi['description'] ?? '' }}">
            @endif

            <div class="mt-8 grid gap-[18px] sm:grid-cols-2">
                @unless ($simpan)
                    <div>
                        <x-aks.tombol varian="b"
                                     type="button"
                                     x-bind:disabled="$store.suara.state === 'listening' || $store.suara.state === 'menunggu'"
                                     x-on:click="$store.suara.dengarSampaiDiam({
                                         label: @js($sumber['label']),
                                         onHasil: (hasil) => { nilai = hasil; },
                                     })">
                            Mulai dengarkan
                        </x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = mulai dengarkan</p>
                    </div>

                    <div>
                        <x-aks.tombol varian="b"
                                     warna="sekunder"
                                     type="button"
                                     x-on:click='$store.suara.speak(@js($sumber["instruksi"]))'>
                            Ulangi instruksi
                        </x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = ulangi instruksi</p>
                    </div>

                    <div>
                        <x-aks.tombol varian="b"
                                     warna="sekunder"
                                     type="button"
                                     x-on:click="$store.suara.speak(nilai !== '' ? nilai : 'Belum ada hasil')">
                            Dengarkan hasil input
                        </x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = dengarkan hasil input</p>
                    </div>
                @endunless

                <div>
                    @if ($simpan)
                        <x-aks.tombol varian="b" warna="sukses" type="submit">Simpan produk</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = simpan produk</p>
                    @else
                        <x-aks.tombol varian="b" type="submit">Lanjut ke {{ strtolower($sumber['berikutnya']) }}</x-aks.tombol>
                        <p class="mt-2 text-[12px] text-muted">Enter = lanjut ke informasi berikutnya</p>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <p class="mt-8 text-[13px] text-muted">
        Navigasi: Tab = berikutnya &bull; Shift + Tab = sebelumnya &bull; Enter = pilih.
        Jika mikrofon tidak tersedia, gunakan input teks.
    </p>
@endsection
