<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAuditLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Blindaje de inmutabilidad estricta: los logs de auditoria no pueden modificarse ni eliminarse.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new DomainException('Los registros de auditoria del sistema son inmutables y no pueden ser modificados.');
        });

        static::deleting(function () {
            throw new DomainException('Los registros de auditoria del sistema son inmutables y no pueden ser eliminados.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForModule($query, ?string $module)
    {
        if ($module && $module !== 'all') {
            return $query->where('module', $module);
        }
        return $query;
    }

    public function scopeForAction($query, ?string $action)
    {
        if ($action && $action !== 'all') {
            return $query->where('action', $action);
        }
        return $query;
    }

    public function scopeForUser($query, ?int $userId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('reference_code', 'like', "%{$term}%")
              ->orWhere('user_name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('action', 'like', "%{$term}%")
              ->orWhere('module', 'like', "%{$term}%");
        });
    }

    /**
     * Calcula el resumen de diferencias entre el estado previo y el posterior.
     */
    public function getDiffSummaryAttribute(): array
    {
        $old = $this->old_values ?: [];
        $new = $this->new_values ?: [];
        $diffs = [];

        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $diffs[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        return $diffs;
    }
}
