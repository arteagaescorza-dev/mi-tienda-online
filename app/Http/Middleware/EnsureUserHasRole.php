<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $activeRole = session('active_role') ?: $user->role;

        $portalRoles = [
            'portal',
            'admin',
            'superadmin',
            'finance',
            'finanzas',
            'warehouse',
            'purchases',
            'logistics',
            'sales',
            'support',
            'operator',
            'all_roles',
        ];

        if ($role === 'portal' && in_array($activeRole, $portalRoles, true)) {
            return $next($request);
        }

        if ($activeRole === $role || in_array($activeRole, ['admin', 'superadmin', 'all_roles'], true)) {
            return $next($request);
        }

        abort(403);
    }
}