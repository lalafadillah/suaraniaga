@extends('layouts.aksesibilitas', ['varian' => 'a'])

@section('content')
    <h1 class="text-[42px] font-bold leading-tight text-ink">Keluar dari akun</h1>
    <p class="mt-4 text-[24px] leading-snug text-muted">
        Anda akan keluar dari akun {{ auth()->user()?->name }}. Anda bisa masuk lagi kapan saja.
    </p>

    <x-aks.pesan class="mt-8" />

    <div class="mt-10 flex flex-col gap-[23px]">
        <form method="POST" action="{{ route('logout.keluar') }}">
            @csrf
            <x-aks.tombol type="submit">Ya, keluar dari akun</x-aks.tombol>
        </form>

        <x-aks.tombol warna="sekunder" href="{{ route('profil') }}">Batal, kembali ke profil</x-aks.tombol>
    </div>
@endsection
