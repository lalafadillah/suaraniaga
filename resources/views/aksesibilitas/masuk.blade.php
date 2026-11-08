@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    @php
        $langkahEmail = $langkah === 'email';
    @endphp

    <h1 class="text-[42px] font-bold leading-tight text-ink">
        {{ $langkahEmail ? 'Masuk ke akun' : 'Masukkan kata sandi' }}
    </h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        {{ $langkahEmail
            ? 'Langkah 1 dari 2. Masukkan alamat email akun Anda.'
            : 'Langkah 2 dari 2. Hanya kolom kata sandi yang ditampilkan.' }}
    </p>

    <x-aks.pesan class="mt-8" />

    <x-aks.langkah class="mt-10" kategori="Login" :sekarang="$langkahEmail ? 1 : 2" :total="2" />

    <form method="POST"
          action="{{ $langkahEmail ? route('login.masuk.email') : route('login.masuk.sandi.store') }}"
          class="mt-8"
          data-form-siap>
        @csrf

        @if ($langkahEmail)
            <x-aks.field name="email"
                         label="Alamat email"
                         type="email"
                         placeholder="Ketik alamat email Anda"
                         autocomplete="email"
                         required />
        @else
            <p class="mb-6 text-[24px] text-muted">
                Email: <span class="font-semibold text-ink">{{ $email }}</span>
            </p>

            <x-aks.field name="password"
                         label="Kata sandi"
                         type="password"
                         placeholder="Ketik kata sandi Anda"
                         autocomplete="current-password"
                         required />
        @endif

        <p class="mt-5 text-[20px] leading-relaxed text-muted">
            Satu kolom saja pada setiap langkah. Pembaca layar membacakan label dan instruksi.
        </p>

        <x-aks.aksi
            class="mt-10"
            :kembali="$langkahEmail ? route('beranda') : route('login.masuk')"
            labelLanjut="Lanjutkan" />
    </form>
@endsection
