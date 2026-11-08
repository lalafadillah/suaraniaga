@php
    $role = $role ?? 'pembeli';

    $menu = $role === 'penjual'
        ? [
            ['key' => 'ringkasan', 'label' => 'Ringkasan', 'route' => 'penjual.dashboard'],
            ['key' => 'produk', 'label' => 'Produk', 'route' => 'penjual.produk'],
            ['key' => 'pesanan-masuk', 'label' => 'Pesanan', 'route' => 'penjual.pesanan'],
            ['key' => 'toko', 'label' => 'Toko', 'route' => 'penjual.toko'],
        ]
        : [
            ['key' => 'beranda', 'label' => 'Beranda', 'route' => 'beranda'],
            ['key' => 'cari', 'label' => 'Cari', 'route' => 'cari'],
            ['key' => 'keranjang', 'label' => 'Keranjang', 'route' => 'keranjang'],
            ['key' => 'profil', 'label' => 'Profil', 'route' => 'profil'],
        ];

    $current = $active ?? 'beranda';
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-surface lg:hidden"
     aria-label="Navigasi utama ringkas">
    <ul class="flex">
        @foreach ($menu as $item)
            <li class="min-w-0 flex-1">
                <a href="{{ route($item['route']) }}"
                   @if ($current === $item['key']) aria-current="page" @endif
                   class="flex min-h-[60px] min-w-0 flex-col items-center justify-center gap-1 px-1 py-2 text-center text-[12px] font-medium
                          {{ $current === $item['key'] ? 'text-brand' : 'text-muted' }}">
                    <span aria-hidden="true" class="h-[3px] w-6 shrink-0 rounded-full {{ $current === $item['key'] ? 'bg-brand' : 'bg-transparent' }}"></span>
                    <span class="w-full truncate">{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>
