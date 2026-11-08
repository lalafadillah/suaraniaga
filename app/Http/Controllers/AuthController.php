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
    private const PERAN_DITERIMA = ['buyer', 'seller', 'pembeli', 'penjual'];

    public function showRegister(): View
    {
        return $this->halaman('guest.register', [
            'title' => 'Daftar Akun',
            'mobileTitle' => 'Daftar Akun',
            'active' => 'profil',
            'context' => 'Daftar akun baru untuk mulai belanja dengan suara',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
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
        return $this->halaman('guest.login', [
            'title' => 'Masuk',
            'mobileTitle' => 'Masuk',
            'active' => 'profil',
            'context' => 'Masuk untuk melanjutkan',
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
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
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

    private function normalisasiPeran(string $role): string
    {
        return in_array($role, [User::ROLE_SELLER, 'penjual'], true)
            ? User::ROLE_SELLER
            : User::ROLE_BUYER;
    }

    private function tujuanPeran(string $role): string
    {
        return $role === User::ROLE_SELLER ? 'seller.dashboard' : 'beranda';
    }
}
