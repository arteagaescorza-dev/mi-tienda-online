<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Services\Governance\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceApiController extends Controller
{
    public function __construct(
        protected AuditTrailService $auditService
    ) {}

    /**
     * Consulta paginada y filtrada del Master Log de Auditoria Omnicanal (TASK-DSH-09).
     */
    public function getAuditLogs(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user ? (session('active_role') ?: $user->role) : null;

        // Solo administradores y superadministradores pueden consultar la bitacora forense
        if (!$role || !in_array($role, ['admin', 'superadmin', 'all_roles', 'portal'], true)) {
            return response()->json([
                'error' => 'Acceso denegado por politica de gobernanza y auditoria RBAC (TASK-DSH-09).',
                'message' => 'Solo los perfiles con rango de superadministrador pueden auditar la bitacora omnicanal.',
            ], 403);
        }

        $module = $request->query('module');
        $action = $request->query('action');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 25);

        $paginator = $this->auditService->getAuditHistory(
            module: $module,
            action: $action,
            term: $search,
            perPage: $perPage
        );

        $stats = $this->auditService->getAuditStats();

        // Enriquecer registros con diff_summary
        $items = collect($paginator->items())->map(function ($log) {
            $array = $log->toArray();
            $array['diff_summary'] = $log->diff_summary;
            return $array;
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Registra una accion manual de auditoria solicitada desde la consola directiva.
     */
    public function recordCustomAction(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !in_array($user->active_role, ['admin', 'superadmin', 'all_roles', 'portal'], true)) {
            return response()->json([
                'error' => 'No autorizado para emitir asientos manuales de auditoria.',
            ], 403);
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'max:50'],
            'module' => ['required', 'string', 'max:50'],
            'reference_code' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'old_values' => ['nullable', 'array'],
            'new_values' => ['nullable', 'array'],
        ]);

        $log = $this->auditService->logAction(
            action: $validated['action'],
            module: $validated['module'],
            referenceCode: $validated['reference_code'] ?? null,
            oldValues: $validated['old_values'] ?? null,
            newValues: $validated['new_values'] ?? null,
            description: $validated['description'],
            user: $user
        );

        return response()->json([
            'success' => true,
            'log_id' => $log->id,
            'message' => 'Evento de auditoria registrado de forma inmutable.',
        ], 201);
    }
}
