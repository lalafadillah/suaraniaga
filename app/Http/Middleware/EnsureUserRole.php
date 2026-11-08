<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, $this->pesan($roles));
        }

        return $next($request);
    }

    /**
     * Pesan penolakan sesuai peran yang diminta, ditulis dalam bahasa Indonesia.
     *
     * @param  array<int, string>  $roles
     */
    private function pesan(array $roles): string
    {
        $label = implode(' atau ', array_map(
            fn (string $role) => match ($role) {
                'buyer' => 'pembeli',
                'seller' => 'penjual',
                'admin' => 'admin',
                default => $role,
            },
            $roles
        ));

        return 'Akses ditolak. Halaman ini khusus '.$label.'.';
    }
}
