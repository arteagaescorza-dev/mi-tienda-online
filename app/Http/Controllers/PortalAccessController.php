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
    public function switchRole(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:superadmin,admin,finance,warehouse,purchases,logistics,sales,support,operator,all_roles,portal'],
        ]);

        session(['active_role' => $validated['role']]);

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
