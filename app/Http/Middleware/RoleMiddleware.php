<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $role = auth()->user()->role;

        if ($role === 'super_admin') {
            return $next($request);
        }

        $aliases = [
            'admin' => ['admin', 'bendahara'],
            'bendahara' => ['bendahara', 'admin'],
            'ketua' => ['ketua', 'rt'],
            'rt' => ['rt', 'ketua', 'rw'],
            'rw' => ['rw'],
            'warga' => ['warga'],
        ];

        $allowedRoles = $roles;
        foreach ($roles as $requestedRole) {
            $allowedRoles = array_merge($allowedRoles, $aliases[$requestedRole] ?? []);
        }

        if (!empty($roles) && !in_array($role, array_unique($allowedRoles), true)) {
            abort(403, 'Akses tidak diizinkan untuk peran akun Anda.');
        }

        return $next($request);
    }
}
