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

        if ($user === null || ! in_array($user->role, $roles, true)) {
            abort(403, 'Halaman ini hanya untuk akun '.implode(' atau ', $this->label($roles)).'.');
        }

        return $next($request);
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function label(array $roles): array
    {
        return array_map(
            fn (string $role) => match ($role) {
                'buyer' => 'pembeli',
                'seller' => 'penjual',
                default => $role,
            },
            $roles
        );
    }
}
