@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Tinjau produk"
        description="Semua produk yang terdaftar, lengkap dengan deskripsi audio untuk pemeriksaan admin."
    />

    @include('admin.navigasi')

    <form method="GET" action="{{ route('admin.products') }}" class="mb-6 rounded-card border border-line bg-surface p-6">
        <div>
            <label for="cari-produk" class="block text-[15px] font-semibold text-ink">Cari produk</label>
            <input type="search"
                   id="cari-produk"
                   name="q"
                   value="{{ $cari }}"
                   class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <x-btn type="submit" variant="primary" size="sm">Cari produk</x-btn>
            <x-btn href="{{ route('admin.products') }}" variant="soft" size="sm">Bersihkan pencarian</x-btn>
        </div>
    </form>

    <h2 class="text-[22px] font-semibold text-ink">Daftar produk</h2>

    <div class="mt-4 overflow-x-auto rounded-card border border-line bg-surface">
        <table class="w-full border-collapse text-left">
            <caption class="px-6 py-4 text-left text-[15px] text-muted">
                {{ $produk->total() }} produk ditemukan.
            </caption>

            <thead>
                <tr class="border-t border-line">
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Nama produk</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Harga</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Penjual</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Deskripsi</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Deskripsi audio</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($produk as $item)
                    <tr class="border-t border-line align-top">
                        <th scope="row" class="px-6 py-4 text-[15px] font-semibold text-ink">{{ $item->name }}</th>
                        <td class="px-6 py-4 text-[15px] text-body">{{ \App\Support\Rupiah::format($item->price) }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">{{ $item->seller?->name ?? 'Penjual tidak ditemukan' }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">{{ $item->description ?: 'Deskripsi belum diisi.' }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">{{ $item->audio_description ?: 'Deskripsi audio belum diisi.' }}</td>
                    </tr>
                @empty
                    <tr class="border-t border-line">
                        <td colspan="5" class="px-6 py-6 text-[15px] text-muted">
                            Tidak ada produk yang cocok dengan pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $produk->links() }}
    </div>
@endsection
