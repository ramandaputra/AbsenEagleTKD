<?php

namespace App\Http\Middleware;

use App\Models\Role;
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
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $userRoleValue = $user->role instanceof Role ? $user->role->name : ($user->role?->name ?? (string) $user->role);

        // If 'admin' is required and user is superadmin, allow it
        if (in_array($userRoleValue, $roles, true) || ($userRoleValue === 'superadmin' && in_array('admin', $roles, true))) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akses ditolak. Peran Anda tidak memiliki izin.'], 403);
        }

        abort(403, 'Akses tidak diizinkan untuk peran akun Anda.');
    }
}
