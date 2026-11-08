@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Checkout"
        description="Lengkapi alamat dan pilih metode pembayaran simulasi."
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

    @if ($barang->isEmpty())
        <section class="rounded-card border border-line bg-surface p-8">
            <h2 class="text-[20px] font-semibold text-ink">Tidak ada barang untukcheckout</h2>
            <p class="mt-2 text-[15px] text-muted">
                Isi keranjang dulu sebelum melanjutkan ke checkout.
            </p>
            <div class="mt-6">
                <x-btn href="{{ route('keranjang') }}">Buka keranjang</x-btn>
            </div>
        </section>
    @else
        <form method="POST"
              action="{{ route('checkout.proses') }}"
              data-form-siap
              class="grid gap-6 lg:grid-cols-[650px_380px]">
            @csrf

            <div class="space-y-4">
                @foreach ([
                    ['id' => 'nama', 'label' => 'Nama penerima', 'value' => old('nama', auth()->user()?->name), 'placeholder' => null, 'wajib' => true],
                    ['id' => 'telepon', 'label' => 'Nomor telepon', 'value' => old('telepon'), 'placeholder' => '08xxxxxxxxxx', 'wajib' => false],
                    ['id' => 'alamat', 'label' => 'Alamat pengiriman', 'value' => old('alamat'), 'placeholder' => 'Jalan, nomor rumah, kecamatan', 'wajib' => true],
                    ['id' => 'catatan', 'label' => 'Catatan untuk penjual', 'value' => old('catatan'), 'placeholder' => 'Opsional — tidak ada catatan', 'wajib' => false],
                ] as $field)
                    <div>
                        <label for="{{ $field['id'] }}" class="block text-[15px] font-semibold text-ink">
                            {{ $field['label'] }}
                            @if ($field['wajib'])
                                <span aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                            @endif
                        </label>

                        <input id="{{ $field['id'] }}"
                               name="{{ $field['id'] }}"
                               type="text"
                               value="{{ $field['value'] }}"
                               placeholder="{{ $field['placeholder'] }}"
                               @if ($field['wajib']) required @endif
                               @if ($errors->has($field['id'])) aria-invalid="true" aria-describedby="{{ $field['id'] }}-galat" @endif
                               class="mt-2 h-[54px] w-full rounded-[9px] border bg-surface px-4 text-base text-body placeholder:text-muted focus:border-brand {{ $errors->has($field['id']) ? 'border-danger' : 'border-line' }}">

                        @if ($errors->has($field['id']))
                            <p id="{{ $field['id'] }}-galat" class="mt-1.5 text-[13px] font-medium text-danger">
                                {{ $errors->first($field['id']) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <aside class="h-fit rounded-card border border-line bg-surface p-6">
                <h2 class="text-[20px] font-semibold text-ink">Metode pembayaran</h2>

                <div class="mt-5">
                    <x-status-pill tone="warning">Simulasi pembayaran</x-status-pill>
                </div>

                <div class="mt-6 space-y-3" role="radiogroup" aria-label="Metode pembayaran">
                    @foreach (\App\Models\Order::METODE_PEMBAYARAN as $nilai => $label)
                        <label class="flex cursor-pointer items-center gap-3 text-base font-medium text-body">
                            <input type="radio"
                                   name="metode"
                                   value="{{ $nilai }}"
                                   @checked(old('metode', 'simulasi') === $nilai)
                                   @if ($errors->has('metode')) aria-invalid="true" @endif
                                   class="h-5 w-5 accent-brand">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                @if ($errors->has('metode'))
                    <p class="mt-2 text-[13px] font-medium text-danger">{{ $errors->first('metode') }}</p>
                @endif

                <h3 class="mt-8 text-[17px] font-semibold text-ink">Rincian barang</h3>

                <ul class="mt-3 space-y-2 text-[15px] text-body">
                    @foreach ($barang as $row)
                        <li class="flex justify-between gap-4">
                            <span>{{ $row['nama'] }} × {{ $row['jumlah'] }}</span>
                            <span class="shrink-0 font-medium">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <hr class="my-6 border-line">

                <p class="text-sm text-muted">Total pembayaran</p>
                <p class="mt-1 text-[22px] font-bold text-brand">Rp {{ number_format($total, 0, ',', '.') }}</p>

                <div class="mt-7">
                    <x-btn type="submit" class="w-full">Buat pesanan</x-btn>
                </div>
            </aside>
        </form>
    @endif
@endsection
