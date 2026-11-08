@extends('layouts.app')

{{-- Warna pesan galat. Token --color-danger sudah ada di app.css branch frontend;
     nilai bawaan dipakai sebagai cadangan selama app.css belum digabung. --}}
@php
    $warnaGalat = 'color: var(--color-danger, #a3231b)';
@endphp

@section('content')
    <x-page-heading
        title="Akun saya"
        description="Data akun yang dipakai saat masuk, memesan, dan mengelola toko."
    />

    @if (session('status'))
        <p role="status" class="mb-6 rounded-card border border-line bg-surface px-5 py-4 text-[15px] text-ink">
            {{ session('status') }}
        </p>
    @endif

    @if ($errors->any())
        <div role="alert" tabindex="-1" class="mb-6 rounded-card border border-line bg-surface px-5 py-4">
            <h2 class="text-[17px] font-semibold text-ink">Periksa kembali isian Anda</h2>
            <ul class="mt-2 list-disc pl-6 text-[15px] text-body">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-card border border-line bg-surface p-6">
        <h2 class="text-[18px] font-semibold text-ink">Data akun</h2>

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
                    {{ match (auth()->user()->role) {
                        'seller' => 'Penjual',
                        'admin' => 'Admin',
                        default => 'Pembeli',
                    } }}
                </dd>
            </div>
        </dl>
    </section>

    <section class="mt-6 rounded-card border border-line bg-surface p-6">
        <h2 class="text-[18px] font-semibold text-ink">Ubah data akun</h2>

        <form method="POST" action="{{ route('akun.update') }}" class="mt-4 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="nama" class="block text-[15px] font-semibold text-ink">Nama lengkap</label>
                <input type="text"
                       id="nama"
                       name="name"
                       value="{{ old('name', auth()->user()->name) }}"
                       required
                       autocomplete="name"
                       @error('name') aria-invalid="true" @enderror
                       class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                @error('name')
                    <p class="mt-1.5 text-[13px] font-medium text-danger" style="{{ $warnaGalat }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-[15px] font-semibold text-ink">Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email', auth()->user()->email) }}"
                       required
                       autocomplete="email"
                       @error('email') aria-invalid="true" @enderror
                       class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                @error('email')
                    <p class="mt-1.5 text-[13px] font-medium text-danger" style="{{ $warnaGalat }}">{{ $message }}</p>
                @enderror
            </div>

            <x-btn type="submit" variant="primary" size="sm">Simpan data akun</x-btn>
        </form>
    </section>

    <section class="mt-6 rounded-card border border-line bg-surface p-6">
        <h2 class="text-[18px] font-semibold text-ink">Ubah kata sandi</h2>

        <form method="POST" action="{{ route('akun.sandi') }}" class="mt-4 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="sandi-lama" class="block text-[15px] font-semibold text-ink">Kata sandi lama</label>
                <input type="password"
                       id="sandi-lama"
                       name="current_password"
                       required
                       autocomplete="current-password"
                       @error('current_password') aria-invalid="true" @enderror
                       class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                @error('current_password')
                    <p class="mt-1.5 text-[13px] font-medium text-danger" style="{{ $warnaGalat }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sandi-baru" class="block text-[15px] font-semibold text-ink">Kata sandi baru</label>
                <p id="sandi-baru-hint" class="mt-1 text-[13px] text-muted">Minimal 8 karakter.</p>
                <input type="password"
                       id="sandi-baru"
                       name="password"
                       required
                       autocomplete="new-password"
                       aria-describedby="sandi-baru-hint"
                       @error('password') aria-invalid="true" @enderror
                       class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                @error('password')
                    <p class="mt-1.5 text-[13px] font-medium text-danger" style="{{ $warnaGalat }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sandi-ulang" class="block text-[15px] font-semibold text-ink">Ulangi kata sandi baru</label>
                <input type="password"
                       id="sandi-ulang"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       @error('password_confirmation') aria-invalid="true" @enderror
                       class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                @error('password_confirmation')
                    <p class="mt-1.5 text-[13px] font-medium text-danger" style="{{ $warnaGalat }}">{{ $message }}</p>
                @enderror
            </div>

            <x-btn type="submit" variant="outline" size="sm">Simpan kata sandi baru</x-btn>
        </form>
    </section>

    <section class="mt-6 rounded-card border border-line bg-surface p-6">
        <h2 class="text-[18px] font-semibold text-ink">Hapus akun</h2>

        <p class="mt-2 text-[15px] text-muted">
            Menghapus akun berarti data pesanan Anda ikut terhapus dan Anda tidak bisa masuk lagi.
        </p>

        <form method="POST" action="{{ route('akun.hapus') }}" class="mt-4 space-y-5">
            @csrf
            @method('DELETE')

            <div>
                <label for="sandi-hapus" class="block text-[15px] font-semibold text-ink">
                    Ketik kata sandi untuk menghapus akun
                </label>
                <input type="password"
                       id="sandi-hapus"
                       name="current_password"
                       required
                       autocomplete="current-password"
                       @error('current_password') aria-invalid="true" @enderror
                       class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                @error('current_password')
                    <p class="mt-1.5 text-[13px] font-medium text-danger" style="{{ $warnaGalat }}">{{ $message }}</p>
                @enderror
            </div>

            <x-btn type="submit" variant="soft" size="sm">Hapus akun saya</x-btn>
        </form>
    </section>

    <section class="mt-6 rounded-card border border-line bg-surface p-6">
        <h2 class="text-[18px] font-semibold text-ink">Keluar dari akun</h2>

        <p class="mt-2 text-[15px] text-muted">
            Sesi Anda di perangkat ini akan diakhiri. Data akun tetap tersimpan.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <x-btn type="submit" variant="outline" size="sm">Keluar dari akun</x-btn>
        </form>
    </section>
@endsection
