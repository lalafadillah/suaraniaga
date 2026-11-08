<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use Illuminate\Support\Facades\Hash;
=======
use Illuminate\Validation\Rule;
>>>>>>> 62399c56dc0e9edfeac77b91d7f572045a1bdf2b
use Illuminate\View\View;

class AuthController extends Controller
{
<<<<<<< HEAD
    public function showLogin(): View
    {
        return view('auth.masuk', ['title' => 'Masuk']);
    }

    public function showRegister(): View
    {
        return view('auth.daftar', ['title' => 'Daftar']);
    }

    public function login(Request $request): RedirectResponse
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'email',
            'password' => 'kata sandi',
        ]);

        $user = User::where('email', $kredensial['email'])->first();

        if (! $user || ! Hash::check($kredensial['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi tidak cocok.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        Auth::login($user, $request->boolean('remember'));

        return redirect()->intended(route('beranda'));
=======
    public function showRegister(): View
    {
        return view('guest.register');
>>>>>>> 62399c56dc0e9edfeac77b91d7f572045a1bdf2b
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
<<<<<<< HEAD
            'role' => ['required', 'in:buyer,seller'],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'password' => 'kata sandi',
            'password_confirmation' => 'ulangi kata sandi',
            'role' => 'peran',
=======
            'role' => ['nullable', Rule::in(User::ROLES)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.in' => 'Peran yang dipilih tidak dikenal.',
>>>>>>> 62399c56dc0e9edfeac77b91d7f572045a1bdf2b
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
<<<<<<< HEAD
            'role' => $data['role'],
        ]);

        $request->session()->regenerate();
        Auth::login($user);

        return redirect()->route('beranda');
=======
            'role' => $data['role'] ?? User::ROLE_BUYER,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('catalog.index'))
            ->with('status', 'Berhasil mendaftar. Selamat datang di SuaraNiaga.');
    }

    public function showLogin(): View
    {
        return view('guest.login');
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

        $remember = $request->boolean('remember');

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('catalog.index'))
            ->with('status', 'Berhasil masuk.');
>>>>>>> 62399c56dc0e9edfeac77b91d7f572045a1bdf2b
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

<<<<<<< HEAD
        return redirect()->route('beranda');
=======
        return redirect()->route('catalog.index')
            ->with('status', 'Anda telah keluar.');
>>>>>>> 62399c56dc0e9edfeac77b91d7f572045a1bdf2b
    }
}
