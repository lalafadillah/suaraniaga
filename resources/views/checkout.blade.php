@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Checkout"
        description="Lengkapi alamat dan pilih metode pembayaran simulasi."
    />

    <form x-data="{ metode: 'simulasi' }" class="grid gap-6 lg:grid-cols-[650px_380px]">
        <div class="space-y-4">
            @foreach ([
                ['id' => 'nama', 'label' => 'Nama penerima', 'value' => 'Moch Bagja', 'placeholder' => null],
                ['id' => 'telepon', 'label' => 'Nomor telepon', 'value' => null, 'placeholder' => '08xxxxxxxxxx'],
                ['id' => 'alamat', 'label' => 'Alamat pengiriman', 'value' => null, 'placeholder' => 'Jalan, nomor rumah, kecamatan'],
                ['id' => 'catatan', 'label' => 'Catatan untuk penjual', 'value' => null, 'placeholder' => 'Opsional — tidak ada catatan'],
            ] as $field)
                <div>
                    <label for="{{ $field['id'] }}" class="block text-sm font-semibold text-ink">{{ $field['label'] }}</label>
                    <input id="{{ $field['id'] }}"
                           name="{{ $field['id'] }}"
                           type="text"
                           value="{{ $field['value'] }}"
                           placeholder="{{ $field['placeholder'] }}"
                           @if ($field['id'] === 'catatan') class="placeholder:text-muted" @endif
                           class="mt-2 h-[54px] w-full rounded-[9px] border border-line bg-surface px-4 text-base text-body focus:border-brand">
                </div>
            @endforeach
        </div>

        <aside class="h-fit rounded-card border border-line bg-surface p-6">
            <h2 class="text-[20px] font-semibold text-ink">Metode pembayaran</h2>

            <div class="mt-5">
                <x-status-pill tone="warning">Simulasi pembayaran</x-status-pill>
            </div>

            <div class="mt-6 space-y-3" role="radiogroup" aria-label="Metode pembayaran">
                @foreach ([
                    ['value' => 'simulasi', 'label' => 'Simulasi pembayaran'],
                    ['value' => 'transfer', 'label' => 'Transfer bank (simulasi)'],
                    ['value' => 'ditempat', 'label' => 'Bayar di tempat (simulasi)'],
                ] as $opsi)
                    <label class="flex cursor-pointer items-center gap-3 text-base font-medium text-body">
                        <input type="radio"
                               name="metode"
                               value="{{ $opsi['value'] }}"
                               x-model="metode"
                               @checked($opsi['value'] === 'simulasi')
                               class="h-5 w-5 accent-brand">
                        {{ $opsi['label'] }}
                    </label>
                @endforeach
            </div>

            <hr class="my-6 border-line">

            <p class="text-sm text-muted">Total pembayaran</p>
            <p class="mt-1 text-[22px] font-bold text-brand">Rp 106.000</p>

            <div class="mt-7">
                <x-btn href="{{ route('pesanan.sukses') }}" class="w-full">Buat pesanan</x-btn>
            </div>
        </aside>
    </form>
@endsection
