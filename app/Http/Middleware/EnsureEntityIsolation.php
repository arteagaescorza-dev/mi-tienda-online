<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEntityIsolation
{
    /**
     * Asegura el aislamiento de datos entre entidades externas B2B (Proveedores y Aliados).
     * Garantiza que ningun proveedor o aliado pueda acceder a recursos fuera de su asignacion.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $expectedEntity  'vendor' | 'ally' | null
     */
    public function handle(Request $request, Closure $next, ?string $expectedEntity = null): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'No autenticado.');
        }

        $activeRole = session('active_role') ?: $user->role;
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);

        // Si se espera un rol de entidad especifico y el usuario no es admin ni tiene dicho rol
        if ($expectedEntity === 'vendor' && !$isGlobalAdmin && $activeRole !== 'vendor') {
            abort(403, 'Acceso restringido unicamente a proveedores autorizados.');
        }

        if ($expectedEntity === 'ally' && !$isGlobalAdmin && $activeRole !== 'ally') {
            abort(403, 'Acceso restringido unicamente a aliados comerciales autorizados.');
        }

        // 1. Verificacion y aislamiento para rol VENDOR
        if ($activeRole === 'vendor') {
            if (empty($user->supplier_id)) {
                if (in_array($user->role, ['admin', 'superadmin', 'portal'], true)) {
                    $supplier = \App\Models\Supplier::first();
                    if ($supplier) {
                        $request->attributes->set('current_supplier_id', (int) $supplier->id);
                        $request->attributes->set('current_supplier', $supplier);
                    }
                } else {
                    abort(403, 'Acceso denegado: Usuario proveedor sin entidad comercial vinculada.');
                }
            } else {
                $supplier = $user->supplier;
                if (!$supplier) {
                    abort(403, 'Acceso denegado: Empresa proveedora no encontrada o inactiva.');
                }

                $request->attributes->set('current_supplier_id', (int) $user->supplier_id);
                $request->attributes->set('current_supplier', $supplier);
            }
        }

        // 2. Verificacion y aislamiento para rol ALLY
        if ($activeRole === 'ally') {
            if (empty($user->ally_id)) {
                if (in_array($user->role, ['admin', 'superadmin', 'portal'], true)) {
                    $ally = \App\Models\Ally::first();
                    if ($ally) {
                        $request->attributes->set('current_ally_id', (int) $ally->id);
                        $request->attributes->set('current_ally', $ally);
                    }
                } else {
                    abort(403, 'Acceso denegado: Usuario aliado sin entidad comercial vinculada.');
                }
            } else {
                $ally = $user->ally;
                if (!$ally) {
                    abort(403, 'Acceso denegado: Entidad comercial de aliado no encontrada o inactiva.');
                }

                $request->attributes->set('current_ally_id', (int) $user->ally_id);
                $request->attributes->set('current_ally', $ally);
            }
        }

        return $next($request);
    }
}
