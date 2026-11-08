<?php

namespace App\Http\Controllers;

use App\Support\ModeAksesibilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD data akun pengguna: baca, ubah, dan hapus akun sendiri.
 */
class AkunController extends Controller
{
    public function index(Request $request): View
    {
        if (ModeAksesibilitas::aktif($request)) {
            return view('aksesibilitas.profil', [
                'title' => 'Profil',
            ]);
        }

        return $this->halaman('akun.index', [
            'title' => 'Akun saya',
            'mobileTitle' => 'Akun saya',
            'active' => 'profil',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ])->save();

        return redirect()->route('akun')
            ->with('status', 'Data akun berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi lama wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi lama tidak cocok.',
            ]);
        }

        $user->password = $data['password'];
        $user->save();

        return redirect()->route('akun')
            ->with('status', 'Kata sandi berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
        ], [
            'current_password.required' => 'Kata sandi wajib diisi untuk menghapus akun.',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi tidak cocok.',
            ]);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')
            ->with('status', 'Akun Anda sudah dihapus.');
    }
}
