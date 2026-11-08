@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Toko saya"
        description="Profil toko, jam operasional, dan area pengiriman."
    />

    <div class="grid gap-6 md:grid-cols-2">
        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Profil toko</h2>
            <p class="mt-2 text-[15px] text-muted">Toko Beras Sejahtera</p>
            <p class="mt-1 text-sm text-muted">Rating 4,8 • Bergabung sejak 2024</p>
        </section>

        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Jam operasional</h2>
            <p class="mt-2 text-[15px] text-muted">Senin – Sabtu, 08.00 – 17.00</p>
            <p class="mt-1 text-sm text-muted">Minggu, 09.00 – 14.00</p>
        </section>

        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Area pengiriman</h2>
            <p class="mt-2 text-[15px] text-muted">Bandung dan sekitarnya</p>
        </section>

        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="text-[18px] font-semibold text-ink">Deskripsi audio toko</h2>
            <p class="mt-2 text-[15px] text-muted">
                Menyediakan beras premium dengan kemasan rapat dan pengiriman tepat waktu.
            </p>

            <div class="mt-4">
                <x-btn variant="soft" size="sm" x-on:click="$store.suara.speak('Toko Beras Sejahtera. Menyediakan beras premium dengan kemasan rapat dan pengiriman tepat waktu.')">
                    Putar deskripsi audio
                </x-btn>
            </div>
        </section>
    </div>
@endsection
