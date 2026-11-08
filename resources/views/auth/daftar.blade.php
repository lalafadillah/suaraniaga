@extends('layouts.auth')

@section('content')
    <div class="rounded-card border border-line bg-surface p-6 lg:p-8">
        <h1 class="text-[26px] font-bold leading-tight text-ink">Daftar akun SuaraNiaga</h1>
        <p class="mt-2 text-[15px] text-muted">
            Satu akun untuk belanja dan mengelola toko. Pilih peran yang paling sesuai.
        </p>

        @if ($errors->any())
            <div id="ringkasan-galat"
                 role="alert"
                 tabindex="-1"
                 class="mt-6 rounded-[10px] border border-danger bg-danger-soft p-4">
                <h2 class="text-[15px] font-semibold text-ink">Periksa kembali isian Anda</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-[15px] text-body">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5" data-auth-form novalidate>
            @csrf

            <x-field name="name"
                     id="name"
                     label="Nama lengkap"
                     autocomplete="name"
                     hint="Nama yang tampil di halaman profil"
                     required />

            <x-field name="email"
                     id="email"
                     label="Email"
                     type="email"
                     inputmode="email"
                     autocomplete="email"
                     hint="Contoh: nama@email.com"
                     required />

            <fieldset>
                <legend class="text-[15px] font-semibold text-ink">Saya mendaftar sebagai</legend>
                <p id="peran-hint" class="mt-1 text-[13px] text-muted">
                    Penjual mendapat halaman kelola produk dan pesanan setelah masuk.
                </p>

                <div class="mt-3 flex flex-wrap gap-3">
                    <label class="flex min-h-[54px] flex-1 cursor-pointer items-center gap-3 rounded-[10px] border border-line-strong bg-surface px-4 has-[:checked]:border-brand has-[:checked]:bg-brand-soft">
                        <input type="radio" name="role" value="buyer" class="h-5 w-5 accent-brand"
                               @checked(old('role', 'buyer') === 'buyer')
                               aria-describedby="peran-hint">
                        <span class="text-[15px] font-medium text-ink">Pembeli</span>
                    </label>

                    <label class="flex min-h-[54px] flex-1 cursor-pointer items-center gap-3 rounded-[10px] border border-line-strong bg-surface px-4 has-[:checked]:border-brand has-[:checked]:bg-brand-soft">
                        <input type="radio" name="role" value="seller" class="h-5 w-5 accent-brand"
                               @checked(old('role') === 'seller')
                               aria-describedby="peran-hint">
                        <span class="text-[15px] font-medium text-ink">Penjual</span>
                    </label>
                </div>
            </fieldset>

            <div>
                <label for="password" class="block text-[15px] font-semibold text-ink">
                    Kata sandi <span aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                </label>
                <p id="password-hint" class="mt-1 text-[13px] text-muted">Minimal 8 karakter.</p>

                <div class="relative mt-2">
                    <input id="password"
                           name="password"
                           type="password"
                           required
                           autocomplete="new-password"
                           aria-describedby="password-hint"
                           @error('password') aria-invalid="true" aria-describedby="password-hint password-galat" @enderror
                           class="field pr-[112px]">
                    <button type="button"
                            data-toggle-password="password"
                            class="absolute inset-y-0 right-0 px-4 text-[13px] font-semibold text-brand underline-offset-4 hover:underline">
                        Lihat
                    </button>
                </div>

                @error('password')
                    <p id="password-galat" class="mt-1.5 text-[13px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-[15px] font-semibold text-ink">
                    Ulangi kata sandi <span aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                </label>

                <div class="relative mt-2">
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           required
                           autocomplete="new-password"
                           @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-galat" @enderror
                           class="field pr-[112px]">
                    <button type="button"
                            data-toggle-password="password_confirmation"
                            class="absolute inset-y-0 right-0 px-4 text-[13px] font-semibold text-brand underline-offset-4 hover:underline">
                        Lihat
                    </button>
                </div>

                @error('password_confirmation')
                    <p id="password_confirmation-galat" class="mt-1.5 text-[13px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-[13px] text-muted">
                Pendaftaran selesai sekali, tanpa kode verifikasi. Peran yang dipilih saat pendaftaran
                berlaku untuk sesi ini.
            </p>

            <button type="submit"
                    class="inline-flex h-[58px] w-full items-center justify-center rounded-[10px] bg-brand px-6 text-base font-semibold text-white transition-colors hover:bg-brand/90">
                Daftar
            </button>

            <p class="text-center text-[13px] text-muted" role="status" data-auth-status></p>
        </form>
    </div>

    <p class="mt-6 text-center text-[15px] text-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand underline underline-offset-4">Masuk di sini</a>
    </p>
@endsection
