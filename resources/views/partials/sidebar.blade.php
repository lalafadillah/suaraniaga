@php
    $role = $role ?? 'pembeli';

    $menu = match ($role) {
        'admin' => [
            ['key' => 'admin-dashboard', 'label' => 'Ringkasan Admin', 'route' => 'admin.dashboard'],
            ['key' => 'admin-pengguna', 'label' => 'Kelola Pengguna', 'route' => 'admin.users'],
            ['key' => 'admin-produk', 'label' => 'Tinjau Produk', 'route' => 'admin.products'],
            ['key' => 'admin-pesanan', 'label' => 'Pantau Pesanan', 'route' => 'admin.orders'],
            ['key' => 'pengaturan', 'label' => 'Pengaturan Aksesibilitas', 'route' => 'pengaturan'],
        ],
        'penjual' => [
            ['key' => 'ringkasan', 'label' => 'Ringkasan', 'route' => 'penjual.dashboard'],
            ['key' => 'produk', 'label' => 'Produk Saya', 'route' => 'penjual.produk'],
            ['key' => 'tambah', 'label' => 'Tambah Produk', 'route' => 'penjual.produk.tambah'],
            ['key' => 'pesanan-masuk', 'label' => 'Pesanan Masuk', 'route' => 'penjual.pesanan'],
            ['key' => 'toko', 'label' => 'Toko Saya', 'route' => 'penjual.toko'],
            ['key' => 'pengaturan', 'label' => 'Pengaturan Aksesibilitas', 'route' => 'pengaturan'],
        ],
        default => [
            ['key' => 'beranda', 'label' => 'Beranda', 'route' => 'beranda'],
            ['key' => 'cari', 'label' => 'Cari Produk', 'route' => 'cari'],
            ['key' => 'keranjang', 'label' => 'Keranjang', 'route' => 'keranjang'],
            ['key' => 'pesanan', 'label' => 'Pesanan Saya', 'route' => 'pesanan'],
            ['key' => 'favorit', 'label' => 'Favorit', 'route' => 'favorit'],
            ['key' => 'profil', 'label' => 'Profil', 'route' => 'profil'],
            ['key' => 'pengaturan', 'label' => 'Pengaturan Aksesibilitas', 'route' => 'pengaturan'],
        ],
    };

    $current = $active ?? 'beranda';
@endphp

<aside class="hidden w-[252px] shrink-0 flex-col border-r border-line bg-surface lg:flex">
    <nav class="flex-1 px-[18px] pt-9" aria-label="Navigasi {{ $role }}">
        <ul>
            @foreach ($menu as $item)
                <li>
                    <a href="{{ route($item['route']) }}"
                       @if ($current === $item['key']) aria-current="page" @endif
                       class="nav-link">{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="px-6 pb-9">
        <form method="POST" action="{{ route('mode.aksesibilitas', 'aksesibilitas') }}" class="mb-6">
            @csrf
            <button type="submit" class="nav-link text-left">
                Mode aksesibilitas
            </button>
        </form>

        <hr class="mb-5 border-line">
        <h2 class="text-[13px] font-semibold text-ink">Navigasi keyboard</h2>
        <p class="mt-1.5 text-xs leading-relaxed text-muted">
            Tekan Tab untuk berpindah. Pembaca layar akan membacakan nama elemen.
        </p>
    </div>
</aside>
