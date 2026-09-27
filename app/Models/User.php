<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Obtiene el rol activo en sesion o el rol asignado en base de datos.
     */
    public function getActiveRoleAttribute(): string
    {
        return session('active_role') ?: ($this->role ?? 'guest');
    }

    /**
     * Determina si el usuario tiene autorizacion para consultar metricas financieras (TASK-RBAC-03).
     */
    public function canAccessFinancialData(): bool
    {
        $role = $this->active_role;
        return in_array($role, ['admin', 'superadmin', 'finance', 'finanzas', 'all_roles', 'portal'], true);
    }

    /**
     * Determina si el usuario pertenece al personal de operaciones de piso.
     */
    public function isOperativeStaff(): bool
    {
        $role = $this->active_role;
        return in_array($role, ['warehouse', 'purchases', 'logistics', 'sales', 'support', 'operator', 'picker', 'packer'], true);
    }

    /**
     * Valida el acceso del usuario a un hub administrativo especifico.
     */
    public function canAccessHub(string $hub): bool
    {
        $role = $this->active_role;

        if (in_array($role, ['admin', 'superadmin', 'all_roles', 'portal'], true)) {
            return true;
        }

        $hubAccessMap = [
            'dashboard' => ['admin', 'superadmin', 'finance', 'warehouse', 'purchases', 'logistics', 'sales', 'support', 'operator', 'portal'],
            'store'     => ['admin', 'superadmin', 'sales', 'marketing', 'portal'],
            'crm'       => ['admin', 'superadmin', 'sales', 'support', 'marketing', 'portal'],
            'inventory' => ['admin', 'superadmin', 'finance', 'warehouse', 'purchases', 'operator', 'portal'],
            'finance'   => ['admin', 'superadmin', 'finance', 'finanzas', 'portal'],
            'logistics' => ['admin', 'superadmin', 'finance', 'warehouse', 'logistics', 'operator', 'portal'],
            'allies'    => ['admin', 'superadmin', 'finance', 'sales', 'marketing', 'portal'],
        ];

        return in_array($role, $hubAccessMap[$hub] ?? [], true);
    }
}
