@extends('layouts.app')

@section('content')
    <x-page-heading
        title="Kelola akun pengguna"
        description="Daftar akun pembeli dan penjual. Akun yang dinonaktifkan tidak bisa masuk lagi."
    />

    @include('admin.navigasi')

    @if (session('status'))
        <p role="status" class="mb-6 rounded-card border border-line bg-surface px-5 py-4 text-[15px] text-ink">
            {{ session('status') }}
        </p>
    @endif

    @if ($errors->any())
        <div role="alert" tabindex="-1" class="mb-6 rounded-card border border-line bg-surface px-5 py-4">
            <h2 class="text-[17px] font-semibold text-ink">Ada kesalahan</h2>
            <ul class="mt-2 list-disc pl-6 text-[15px] text-body">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.users') }}" class="mb-6 rounded-card border border-line bg-surface p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="cari-pengguna" class="block text-[15px] font-semibold text-ink">Cari nama atau email</label>
                <input type="search"
                       id="cari-pengguna"
                       name="q"
                       value="{{ $cari }}"
                       class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
            </div>

            <div>
                <label for="filter-peran" class="block text-[15px] font-semibold text-ink">Peran</label>
                <select id="filter-peran" name="role" class="mt-2 w-full rounded-[10px] border border-line bg-white px-4 py-3 text-[15px] text-ink">
                    <option value="">Semua peran</option>
                    <option value="buyer" @selected($peran === 'buyer')>Pembeli</option>
                    <option value="seller" @selected($peran === 'seller')>Penjual</option>
                </select>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <x-btn type="submit" variant="primary" size="sm">Terapkan filter</x-btn>
            <x-btn href="{{ route('admin.users') }}" variant="soft" size="sm">Bersihkan filter</x-btn>
        </div>
    </form>

    <h2 class="text-[22px] font-semibold text-ink">Akun pembeli dan penjual</h2>

    <div class="mt-4 overflow-x-auto rounded-card border border-line bg-surface">
        <table class="w-full border-collapse text-left">
            <caption class="px-6 py-4 text-left text-[15px] text-muted">
                {{ $pengguna->total() }} akun ditemukan.
            </caption>

            <thead>
                <tr class="border-t border-line">
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Nama</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Email</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Peran</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Status akun</th>
                    <th scope="col" class="px-6 py-3 text-[15px] font-semibold text-ink">Tindakan</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pengguna as $akun)
                    <tr class="border-t border-line">
                        <th scope="row" class="px-6 py-4 text-[15px] font-semibold text-ink">{{ $akun->name }}</th>
                        <td class="px-6 py-4 text-[15px] text-body">{{ $akun->email }}</td>
                        <td class="px-6 py-4 text-[15px] text-body">
                            {{ $akun->role === 'seller' ? 'Penjual' : 'Pembeli' }}
                        </td>
                        <td class="px-6 py-4 text-[15px] text-body">
                            <x-status-pill :tone="$akun->isActive() ? 'success' : 'warning'">
                                {{ $akun->isActive() ? 'Aktif' : 'Nonaktif' }}
                            </x-status-pill>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.users.status', $akun) }}">
                                @csrf
                                @method('PATCH')
                                <x-btn type="submit" variant="outline" size="sm">
                                    {{ $akun->isActive() ? 'Nonaktifkan akun '.$akun->name : 'Aktifkan akun '.$akun->name }}
                                </x-btn>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t border-line">
                        <td colspan="5" class="px-6 py-6 text-[15px] text-muted">
                            Tidak ada akun yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $pengguna->links() }}
    </div>
@endsection
