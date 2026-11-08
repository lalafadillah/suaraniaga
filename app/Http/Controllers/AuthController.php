<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Peran yang boleh dipilih saat pendaftaran publik.
     * Peran admin hanya bisa dibuat lewat perintah `php artisan admin:create`.
     */
    private const PERAN_DITERIMA = [User::ROLE_BUYER, User::ROLE_SELLER];

    /**
     * Alias bahasa Indonesia untuk peran, dipakai form pendaftaran lama.
     */
    private const ALIAS_PERAN = [
        'pembeli' => User::ROLE_BUYER,
        'penjual' => User::ROLE_SELLER,
    ];

    public function showRegister(): View
    {
        return view('auth.daftar', [
            'title' => 'Daftar Akun',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        if ($request->filled('role')) {
            $request->merge([
                'role' => self::ALIAS_PERAN[$request->input('role')] ?? $request->input('role'),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', Rule::in(self::PERAN_DITERIMA)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.in' => 'Peran yang dipilih tidak dikenal.',
        ]);

        $role = $this->normalisasiPeran($data['role'] ?? User::ROLE_BUYER);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->tujuanPeran($role))
            ->with('status', 'Berhasil mendaftar. Selamat datang di SuaraNiaga.');
    }

    public function showLogin(): View
    {
        return view('auth.masuk', [
            'title' => 'Masuk',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            return redirect()->route($this->halamanMasuk($request))
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        if (! $request->user()->isActive()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($this->halamanMasuk($request))
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->tujuanPeran($request->user()->role)))
            ->with('status', 'Berhasil masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')
            ->with('status', 'Anda telah keluar.');
    }

    /**
     * Halaman masuk yang dipakai untuk mengembalikan galat, sesuai alias yang
     * sedang dibuka pengguna (/masuk atau /login).
     */
    private function halamanMasuk(Request $request): string
    {
        $referer = (string) $request->headers->get('referer');

        return str_contains($referer, '/masuk') ? 'login.masuk' : 'login';
    }

    private function normalisasiPeran(string $role): string
    {
        return in_array($role, self::PERAN_DITERIMA, true) ? $role : User::ROLE_BUYER;
    }

    private function tujuanPeran(string $role): string
    {
        return match ($role) {
            User::ROLE_SELLER => 'seller.dashboard',
            User::ROLE_ADMIN => 'admin.dashboard',
            default => 'beranda',
        };
    }
}
