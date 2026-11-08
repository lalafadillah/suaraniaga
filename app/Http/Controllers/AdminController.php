<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman admin: ringkasan data, kelola akun, tinjau produk, dan pantau pesanan.
 */
class AdminController extends Controller
{
    private const PERAN_AKUN = [User::ROLE_BUYER, User::ROLE_SELLER];

    public function dashboard(): View
    {
        $jumlahPengguna = [
            User::ROLE_BUYER => User::where('role', User::ROLE_BUYER)->count(),
            User::ROLE_SELLER => User::where('role', User::ROLE_SELLER)->count(),
            User::ROLE_ADMIN => User::where('role', User::ROLE_ADMIN)->count(),
        ];

        $jumlahProduk = Product::count();
        $jumlahPesanan = Order::count();

        $pesananPerStatus = Order::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->all();

        $statusPesanan = collect(Order::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($pesananPerStatus[$status] ?? 0)])
            ->all();

        return $this->halaman('admin.dashboard', [
            'title' => 'Dasbor Admin',
            'mobileTitle' => 'Dasbor Admin',
            'active' => 'admin-dashboard',
            'jumlahPengguna' => $jumlahPengguna,
            'jumlahProduk' => $jumlahProduk,
            'jumlahPesanan' => $jumlahPesanan,
            'statusPesanan' => $statusPesanan,
        ]);
    }

    public function users(Request $request): View
    {
        $peran = $request->query('role');
        $peran = in_array($peran, self::PERAN_AKUN, true) ? $peran : null;

        $cari = trim((string) $request->query('q', ''));

        $pengguna = User::query()
            ->whereIn('role', self::PERAN_AKUN)
            ->when($peran !== null, fn ($query) => $query->where('role', $peran))
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($query) use ($cari) {
                    $query->where('name', 'like', '%'.$cari.'%')
                        ->orWhere('email', 'like', '%'.$cari.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return $this->halaman('admin.users', [
            'title' => 'Kelola Akun Pengguna',
            'mobileTitle' => 'Kelola Akun',
            'active' => 'admin-pengguna',
            'pengguna' => $pengguna,
            'peran' => $peran,
            'cari' => $cari,
        ]);
    }

    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin() || $request->user()->id === $user->id) {
            abort(403, 'Akses ditolak. Akun admin tidak dapat dinonaktifkan dari halaman ini.');
        }

        $user->is_active = ! $user->isActive();
        $user->save();

        $pesan = $user->isActive()
            ? 'Akun '.$user->name.' berhasil diaktifkan kembali.'
            : 'Akun '.$user->name.' berhasil dinonaktifkan.';

        return redirect()->back()->with('status', $pesan);
    }

    public function products(Request $request): View
    {
        $cari = trim((string) $request->query('q', ''));

        $produk = Product::query()
            ->with('seller')
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($query) use ($cari) {
                    $query->where('name', 'like', '%'.$cari.'%')
                        ->orWhere('description', 'like', '%'.$cari.'%')
                        ->orWhere('audio_description', 'like', '%'.$cari.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return $this->halaman('admin.products', [
            'title' => 'Tinjau Produk',
            'mobileTitle' => 'Tinjau Produk',
            'active' => 'admin-produk',
            'produk' => $produk,
            'cari' => $cari,
        ]);
    }

    public function orders(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, Order::STATUSES, true) ? $status : null;

        $pesanan = Order::query()
            ->with(['product', 'buyer', 'product.seller'])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return $this->halaman('admin.orders', [
            'title' => 'Pantau Pesanan',
            'mobileTitle' => 'Pantau Pesanan',
            'active' => 'admin-pesanan',
            'pesanan' => $pesanan,
            'status' => $status,
        ]);
    }
}
