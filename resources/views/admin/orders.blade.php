@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Pantau pesanan"
        description="Semua pesanan yang masuk, dari pembeli, penjual, sampai status pengiriman."
    />


    <form method="GET" action="{{ route('admin.orders') }}" class="mb-6 rounded-card border border-line bg-surface p-6">
        <div>
            <label for="filter-status" class="block text-[15px] font-semibold text-ink">Status pesanan</label>
            <select id="filter-status" name="status" class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                <option value="">Semua status</option>
                @foreach (\App\Models\Order::STATUSES as $pilihan)
                    <option value="{{ $pilihan }}" @selected($status === $pilihan)>{{ ucfirst($pilihan) }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <x-btn type="submit" variant="primary" size="sm">Terapkan filter</x-btn>
            <x-btn href="{{ route('admin.orders') }}" variant="soft" size="sm">Bersihkan filter</x-btn>
        </div>
    </form>

    <h2 class="text-[22px] font-semibold text-ink">Daftar pesanan</h2>

    <div class="mt-4 overflow-x-auto rounded-card border border-line bg-surface">
        <table class="w-full border-collapse text-left">
            <caption class="px-6 py-4 text-left text-[15px] text-muted">
                {{ $pesanan->total() }} pesanan ditemukan.
            </caption>

            <thead>
                <tr class="border-t border-line">
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Nomor pesanan</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Produk</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Pembeli</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Penjual</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Total</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pesanan as $item)
                    <tr class="border-t border-line">
                        <th scope="row" class="px-6 py-4 text-[15px] font-semibold text-ink">#{{ $item->id }}</th>
                        <td class="px-6 py-4 text-[15px] text-body">{{ $item->product?->name ?? 'Produk tidak ditemukan' }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">{{ $item->buyer?->name ?? 'Pembeli tidak ditemukan' }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">{{ $item->product?->seller?->name ?? 'Penjual tidak ditemukan' }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">{{ \App\Support\Rupiah::format($item->total_price) }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">
                            <x-status-pill tone="info">{{ ucfirst($item->status) }}</x-status-pill>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t border-line">
                        <td colspan="6" class="px-6 py-6 text-[15px] text-muted">
                            Belum ada pesanan yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $pesanan->links() }}
    </div>
@endsection
