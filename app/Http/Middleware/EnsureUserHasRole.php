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
            return redirect()->guest(route('login'));
        }

        // Superadmin, admin and portal base staff accounts never get locked out of the portal
        if (in_array($user->role, ['admin', 'superadmin', 'portal'], true)) {
            return $next($request);
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
            'marketing',
            'packer',
            'picker',
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