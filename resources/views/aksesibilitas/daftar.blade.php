@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    @php
        $urutan = \App\Http\Controllers\AuthController::LANGKAH_DAFTAR;
        $nomor = array_search($langkah, $urutan, true) + 1;
        $total = 6;
        $pilihPeran = $langkah === 'peran';
        $sumber = $daftar[$langkah];
        $namaField = [
            'nama' => 'name',
            'email' => 'email',
            'telepon' => 'phone',
            'alamat' => 'address',
            'sandi' => 'password',
            'konfirmasi' => 'password_confirmation',
        ][$langkah] ?? null;
        $terisi = $terisi ?? [];
        $kembali = $nomor <= 1
            ? route('beranda')
            : route('daftar.langkah', $urutan[$nomor - 2]);
    @endphp

    <h1 class="text-[42px] font-bold leading-tight text-ink">{{ $sumber['judul'] }}</h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">{{ $sumber['instruksi'] }}</p>

    <x-aks.pesan class="mt-8" />

    @unless ($pilihPeran)
        <x-aks.langkah class="mt-10" kategori="Pendaftaran" :sekarang="$nomor" :total="$total" />
    @endunless

    <form method="POST"
          action="{{ $pilihPeran ? route('daftar.selesai') : route('daftar.langkah.simpan', $langkah) }}"
          class="mt-8"
          data-form-siap>
        @csrf

        @if ($pilihPeran)
            <fieldset x-data="{ peran: '{{ old('role', '') }}' }}">
                <legend class="text-[28px] font-bold text-ink">{{ $sumber['label'] }}</legend>

                <div class="mt-4 flex flex-col gap-[23px]">
                    <label :class="peran === 'buyer'
                            ? 'border-[5px] border-accent bg-brand text-white'
                            : 'border-2 border-brand bg-brand text-white'"
                           class="flex min-h-[100px] cursor-pointer items-center gap-5 rounded-[14px] px-8 text-[25px] font-semibold">
                        <input type="radio" name="role" value="buyer" x-model="peran" class="h-7 w-7" required>
                        Pembeli
                    </label>

                    <label :class="peran === 'seller'
                            ? 'border-[5px] border-accent bg-brand text-white'
                            : 'border-2 border-brand bg-brand text-white'"
                           class="flex min-h-[100px] cursor-pointer items-center gap-5 rounded-[14px] px-8 text-[25px] font-semibold">
                        <input type="radio" name="role" value="seller" x-model="peran" class="h-7 w-7" required>
                        Penjual
                    </label>
                </div>

                @error('role')
                    <p class="mt-3 text-[20px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </fieldset>
        @else
            <x-aks.field :name="$namaField"
                         :label="$sumber['label']"
                         :type="$sumber['tipe']"
                         :placeholder="$sumber['placeholder']"
                         :value="$terisi[$namaField] ?? null"
                         required />
        @endif

        <p class="mt-5 text-[20px] leading-relaxed text-muted">
            Satu kolom saja pada setiap langkah. Pembaca layar membacakan label dan instruksi.
        </p>

        @if ($pilihPeran)
            <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <x-aks.tombol warna="sekunder" :href="$kembali" class="sm:w-[300px]">Kembali</x-aks.tombol>
                <x-aks.tombol type="submit" class="sm:w-[300px]">Lanjutkan</x-aks.tombol>
            </div>
        @else
            <x-aks.aksi class="mt-10" :kembali="$kembali" labelLanjut="Lanjutkan" />
        @endif
    </form>
@endsection
