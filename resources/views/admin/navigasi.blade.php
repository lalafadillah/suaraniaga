@php
    $menu = [
        ['key' => 'admin-dashboard', 'label' => 'Ringkasan', 'route' => 'admin.dashboard'],
        ['key' => 'admin-pengguna', 'label' => 'Pengguna', 'route' => 'admin.users'],
        ['key' => 'admin-produk', 'label' => 'Produk', 'route' => 'admin.products'],
        ['key' => 'admin-pesanan', 'label' => 'Pesanan', 'route' => 'admin.orders'],
    ];
@endphp

<nav aria-label="Navigasi admin" class="mb-6">
    <ul class="flex flex-wrap gap-3">
        @foreach ($menu as $item)
            <li>
                <a href="{{ route($item['route']) }}"
                   @if (($active ?? '') === $item['key']) aria-current="page" @endif
                   class="inline-flex items-center rounded-[10px] border border-line bg-surface px-4 py-2 text-[15px] font-semibold text-ink hover:bg-brand-soft">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
