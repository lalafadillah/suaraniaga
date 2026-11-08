@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Profil"
        description="Data akun dan preferensi dasar yang dipakai saat checkout."
    />

    <div class="grid gap-6 md:grid-cols-2">
        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Akun</h2>

            @auth
                <dl class="mt-4 space-y-3 text-[15px]">
                    <div class="flex gap-3">
                        <dt class="w-[140px] shrink-0 text-muted">Nama</dt>
                        <dd class="font-medium text-ink">{{ auth()->user()->name }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="w-[140px] shrink-0 text-muted">Email</dt>
                        <dd class="font-medium text-ink">{{ auth()->user()->email }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="w-[140px] shrink-0 text-muted">Peran</dt>
                        <dd class="font-medium text-ink">
                            {{ auth()->user()->role === 'seller' ? 'Penjual' : 'Pembeli' }}
                        </dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('logout') }}" class="mt-6">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-[10px] border border-brand bg-surface px-5 text-[15px] font-semibold text-brand transition-colors hover:bg-brand-soft">
                        Keluar dari akun
                    </button>
                </form>
            @else
                <p class="mt-3 text-[15px] text-muted">
                    Anda belum masuk. Masuk untuk melihat data akun dan pesanan Anda.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <x-btn href="{{ route('login') }}" variant="primary" size="sm">Masuk</x-btn>
                    <x-btn href="{{ route('register') }}" variant="outline" size="sm">Daftar akun</x-btn>
                </div>
            @endauth
        </section>

        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Data penerima</h2>

            <dl class="mt-4 space-y-3 text-[15px]">
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
