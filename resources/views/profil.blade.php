@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Profil"
        description="Data akun dan preferensi dasar yang dipakai saat checkout."
    />

    <div class="grid gap-6 md:grid-cols-2">
        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Data penerima</h2>

            <dl class="mt-4 space-y-3 text-[15px]">
                <div class="flex gap-3">
                    <dt class="w-[140px] shrink-0 text-muted">Nama</dt>
                    <dd class="font-medium text-ink">Moch Bagja</dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-[140px] shrink-0 text-muted">Nomor telepon</dt>
                    <dd class="font-medium text-ink">08xxxxxxxxxx</dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-[140px] shrink-0 text-muted">Alamat</dt>
                    <dd class="font-medium text-ink">Jalan, nomor rumah, kecamatan</dd>
                </div>
            </dl>

            <div class="mt-6">
                <x-btn variant="outline" size="sm">Ubah data</x-btn>
            </div>
        </section>

        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Preferensi</h2>

            <p class="mt-2 text-[15px] text-muted">
                Ukuran teks, kontras, dan kecepatan suara diatur di halaman pengaturan aksesibilitas.
            </p>

            <div class="mt-5">
                <x-btn href="{{ route('pengaturan') }}" variant="outline" size="sm">Buka pengaturan aksesibilitas</x-btn>
            </div>
        </section>
    </div>
@endsection
