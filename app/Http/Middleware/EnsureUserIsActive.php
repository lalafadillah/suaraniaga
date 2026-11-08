<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengeluarkan pengguna yang akunnya dinonaktifkan admin.
 *
 * Admin bisa menonaktifkan akun kapan saja, jadi sesi yang masih hidup harus
 * ikut berakhir pada permintaan berikutnya.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->isActive()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.']);
        }

        return $next($request);
    }
}
