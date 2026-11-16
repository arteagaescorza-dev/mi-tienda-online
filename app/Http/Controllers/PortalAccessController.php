<?php

namespace App\Http\Controllers;

class PortalAccessController extends Controller
{
    public function userLogin()
    {
        return view('auth.user-login');
    }

    public function clientLogin()
    {
        return view('auth.client-login');
    }

    /**
     * Alterna el rol activo de la sesion para pruebas y gobernanza RBAC (TASK-RBAC-02, TASK-RBAC-03).
     */
    public function switchRole(\Illuminate\Http\Request $request, \App\Services\Governance\AuditTrailService $auditService)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['superadmin', 'admin', 'portal'], true), 403, 'No autorizado para conmutar roles operativos.');

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:superadmin,admin,finance,warehouse,purchases,logistics,sales,support,operator,all_roles,portal,vendor,ally'],
        ]);

        $previousRole = session('active_role') ?: $user->role;
        session(['active_role' => $validated['role']]);

        $auditService->logRoleSwitch($user, $previousRole, $validated['role']);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'active_role' => $validated['role'],
                'can_access_financials' => auth()->user()?->canAccessFinancialData() ?? false,
                'message' => 'Rol activo cambiado a ' . $validated['role'],
            ]);
        }

        return back()->with('success', 'Rol operativo actualizado a ' . $validated['role']);
    }
}
