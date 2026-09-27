<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceFinancialPrivacy
{
    /**
     * Roles con restriccion estricta de datos financieros (TASK-RBAC-03).
     * El personal de operaciones, logistica, almacen y atencion jamas debe visualizar margenes ni costos.
     */
    protected array $restrictedRoles = [
        'warehouse',
        'purchases',
        'logistics',
        'sales',
        'support',
        'operator',
        'picker',
        'packer',
        'customer',
    ];

    /**
     * Claves que deben ser removidas de las respuestas JSON para roles con restriccion financiera (TASK-RBAC-03).
     */
    protected array $confidentialFinancialKeys = [
        'margin_net',
        'financial_margin_amount',
        'profit_margin_pct',
        'financial_costs',
        'gross_revenue',
        'sat_taxes',
        'taxable_base',
        'net_iva_payable',
        'platform_retention_isr_1',
        'platform_retention_iva_8',
        'iva_transferred_16',
        'overcharge_amount_mxn',
    ];

    /**
     * Claves de costo adicionales que deben ocultarse a roles de piso (almacen, logistica, atencion).
     */
    protected array $floorRestrictedCostKeys = [
        'unit_cost',
        'cost',
        'cost_amount',
        'debit_note_amount',
        'payable_net_amount',
    ];

    public function handle(Request $request, Closure $next, ?string $mode = null): Response
    {
        $user = $request->user();
        $activeRole = $request->header('X-User-Role')
            ?: (session('active_role') ?: ($user ? $user->role : null));

        $isRestricted = $activeRole && in_array($activeRole, $this->restrictedRoles, true);

        // Bloqueo estricto para rutas dedicadas a finanzas si el rol activo es restringido
        $isFinancialRoute = $request->routeIs('portal.finance*') ||
            $request->is('admin/finanzas*') ||
            $request->is('api/v1/finance*');

        if ($isFinancialRoute && $isRestricted) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Acceso denegado por politica de privacidad financiera (TASK-RBAC-03).',
                    'message' => 'Tu rol actual no cuenta con autorizacion para consultar metricas contables, costos o fiscales.',
                    'role_attempted' => $activeRole,
                ], 403);
            }

            return redirect()->route('portal.dashboard')
                ->with('error', 'Acceso denegado a Finanzas: Politica estricta de privacidad financiera RBAC (TASK-RBAC-03).');
        }

        $response = $next($request);

        // Sanitizacion reactiva en respuestas JSON para roles restringidos
        if ($isRestricted && $response instanceof JsonResponse) {
            $data = $response->getData(true);
            if (is_array($data)) {
                $sanitized = $this->sanitizeDataRecursively($data, $activeRole);
                $response->setData($sanitized);
            }
        }

        return $response;
    }

    /**
     * Limpia de forma recursiva cualquier campo financiero sensible.
     */
    protected function sanitizeDataRecursively(mixed $data, string $role): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $forbiddenKeys = $this->confidentialFinancialKeys;

        if (in_array($role, ['warehouse', 'logistics', 'sales', 'support', 'operator'], true)) {
            $forbiddenKeys = array_merge($forbiddenKeys, $this->floorRestrictedCostKeys);
        }

        $cleaned = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $forbiddenKeys, true)) {
                continue; // Se elimina el campo sensible
            }

            if (is_array($value)) {
                $cleaned[$key] = $this->sanitizeDataRecursively($value, $role);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }
}
