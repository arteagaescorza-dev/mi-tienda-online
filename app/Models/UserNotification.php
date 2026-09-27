<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    use HasFactory;

    protected $table = 'user_notifications';

    protected $guarded = ['id'];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = [
        'badge_color',
        'time_ago',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operationalTask(): BelongsTo
    {
        return $this->belongsTo(OperationalTask::class, 'operational_task_id');
    }

    /**
     * Color distintivo del indicador segun severidad de la alerta.
     */
    public function getBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'critical', 'urgent' => 'red',
            'warning' => 'amber',
            'success' => 'emerald',
            default => 'blue',
        };
    }

    /**
     * Tiempo transcurrido formateado en espanol para visualizacion operativa.
     */
    public function getTimeAgoAttribute(): string
    {
        $diffMinutes = (int) $this->created_at?->diffInMinutes(now());

        if ($diffMinutes < 1) {
            return 'Hace un momento';
        }

        if ($diffMinutes < 60) {
            return "Hace {$diffMinutes} min";
        }

        $diffHours = (int) floor($diffMinutes / 60);
        if ($diffHours < 24) {
            return "Hace {$diffHours} " . ($diffHours === 1 ? 'hora' : 'horas');
        }

        $diffDays = (int) floor($diffHours / 24);
        return "Hace {$diffDays} " . ($diffDays === 1 ? 'dia' : 'dias');
    }

    /**
     * Scope para filtrar notificaciones pendientes de atencion.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope para filtrar notificaciones destinadas al usuario o su rol operativo actual.
     * Respeta politicas estrictas de privacidad financiera (TASK-RBAC-03).
     */
    public function scopeForUserOrRole($query, User $user, ?string $role = null)
    {
        $role = $role ?: ($user->active_role ?: $user->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'all_roles', 'portal'], true);

        $query->where(function ($q) use ($user, $role, $isAdmin) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($sub) use ($role, $isAdmin) {
                  $sub->whereNull('user_id');
                  if (!$isAdmin) {
                      $sub->where(function ($roleGroup) use ($role) {
                          $roleGroup->whereNull('target_role')
                                    ->orWhere('target_role', $role);
                      });
                  }
              });
        });

        // Restriccion estricta: roles operativos restringidos no pueden ver alertas financieras confidenciales
        if (!$user->canAccessFinancialData()) {
            $query->where(function ($sec) {
                $sec->where('category', '!=', 'finance')
                    ->where(function ($roleCheck) {
                        $roleCheck->whereNull('target_role')
                                  ->orWhere('target_role', '!=', 'finance');
                    });
            });
        }

        return $query;
    }

    /**
     * Scope por categoria de negocio.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope por severidad.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Marca la notificacion como leida.
     */
    public function markAsRead(): bool
    {
        $this->is_read = true;
        $this->read_at = now();
        return $this->save();
    }
}
