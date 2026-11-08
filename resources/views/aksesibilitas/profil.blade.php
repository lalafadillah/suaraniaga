@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    @php
        $pengguna = auth()->user();
        $penjual = $pengguna?->isSeller() ?? false;
    @endphp

    <h1 class="text-[42px] font-bold leading-tight text-ink">
        {{ $penjual ? 'Profil toko dan keluar' : 'Profil dan keluar' }}
    </h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        Data akun Anda. Pilih tindakan yang ingin dijalankan.
    </p>

    <section class="mt-10 rounded-[14px] border-[3px] border-ink bg-white p-[30px]">
        <h2 class="text-[20px] font-bold uppercase text-brand">Data akun</h2>

        <dl class="mt-4 space-y-4 text-[25px] text-ink">
            <div>
                <dt class="text-[20px] font-medium text-muted">Nama</dt>
                <dd class="font-semibold">{{ $pengguna?->name }}</dd>
            </div>
            <div>
                <dt class="text-[20px] font-medium text-muted">Alamat email</dt>
                <dd class="font-semibold">{{ $pengguna?->email }}</dd>
            </div>
            <div>
                <dt class="text-[20px] font-medium text-muted">Nomor telepon</dt>
                <dd class="font-semibold">{{ $pengguna?->phone ?: 'Belum diisi' }}</dd>
            </div>
            <div>
                <dt class="text-[20px] font-medium text-muted">Alamat lengkap</dt>
                <dd class="font-semibold">{{ $pengguna?->address ?: 'Belum diisi' }}</dd>
            </div>
            <div>
                <dt class="text-[20px] font-medium text-muted">Peran</dt>
                <dd class="font-semibold">{{ $penjual ? 'Penjual' : 'Pembeli' }}</dd>
            </div>
        </dl>
    </section>

    <div class="mt-10 flex flex-col gap-[23px]">
        <x-aks.tombol warna="biru" href="{{ route('pengaturan') }}">Bantuan aksesibilitas</x-aks.tombol>

        <x-aks.tombol warna="sekunder" href="{{ route('logout.konfirmasi') }}">Keluar dari akun</x-aks.tombol>
    </div>
@endsection
