@extends('layouts.auth')

@section('content')
    <div class="rounded-card border border-line bg-surface p-6 lg:p-8">
        <h1 class="text-[26px] font-bold leading-tight text-ink">Masuk ke SuaraNiaga</h1>
        <p class="mt-2 text-[15px] text-muted">
            Belanja mandiri dengan suara. Isi email dan kata sandi, atau ucapkan email lewat mikrofon.
        </p>

        {{-- Ringkasan error validasi di atas form, fokus dipindah ke sini --}}
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

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5" data-auth-form novalidate>
            @csrf

            <div x-data="{ merekam: false }">
                <x-field name="email"
                         id="email"
                         label="Email"
                         type="email"
                         inputmode="email"
                         autocomplete="email"
                         hint="Contoh: nama@email.com"
                         required />

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                    <button type="button"
                            x-on:click="merekam ? $store.suara.stop() : $store.suara.start((value) => {
                                merekam = false;
                                document.getElementById('email').value = value;
                                document.getElementById('email').focus();
                                $store.announcer.say('Email diisi dari suara');
                            })"
                            x-bind:aria-pressed="merekam"
                            x-bind:disabled="!$store.suara.supported"
                            class="text-[13px] font-semibold text-brand underline underline-offset-4 hover:no-underline disabled:cursor-not-allowed disabled:text-muted disabled:no-underline">
                        <span x-show="!merekam">Sebutkan email</span>
                        <span x-show="merekam" x-cloak>Berhenti merekam</span>
                    </button>

                    <span class="text-[13px] text-muted">atau ketik manual di atas</span>
                </div>

                <p class="mt-2 text-[13px] text-muted" x-show="!$store.suara.supported" x-cloak>
                    Browser Anda belum mendukung input suara. Ketik email seperti biasa.
                </p>
                <p class="mt-2 text-[13px] text-danger" x-show="$store.suara.error" x-cloak x-text="$store.suara.error" role="alert"></p>
            </div>

            <div>
                <label for="password" class="block text-[15px] font-semibold text-ink">
                    Kata sandi <span aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                </label>

                <div class="relative mt-2">
                    <input id="password"
                           name="password"
                           type="password"
                           required
                           autocomplete="current-password"
                           @error('password') aria-invalid="true" aria-describedby="password-galat" @enderror
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

            <div class="flex items-start gap-3">
                <input id="ingat-saya"
                       name="remember"
                       type="checkbox"
                       value="1"
                       @checked(old('remember'))
                       class="mt-1 h-5 w-5 shrink-0 rounded border-line-strong accent-brand">
                <label for="ingat-saya" class="text-[15px] text-body">Ingat saya di perangkat ini</label>
            </div>

            <button type="submit"
                    class="inline-flex h-[58px] w-full items-center justify-center rounded-[10px] bg-brand px-6 text-base font-semibold text-white transition-colors hover:bg-brand/90">
                Masuk
            </button>

            <p class="text-center text-[13px] text-muted" role="status" data-auth-status></p>
        </form>
    </div>

    <p class="mt-6 text-center text-[15px] text-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand underline underline-offset-4">Daftar sekarang</a>
    </p>
@endsection
