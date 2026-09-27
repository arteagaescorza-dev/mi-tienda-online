<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalTask extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'action_payload' => 'array',
    ];

    protected $appends = [
        'sla_status',
        'due_time_formatted',
    ];

    public function shiftLogs(): HasMany
    {
        return $this->hasMany(ShiftLog::class, 'operational_task_id');
    }

    /**
     * Calcula el estado de urgencia del SLA segun el tiempo restante para el corte.
     */
    public function getSlaStatusAttribute(): string
    {
        if ($this->status === 'completed') {
            return 'completed';
        }

        if (!$this->due_at) {
            return 'ontime';
        }

        $now = now();
        if ($now->greaterThanOrEqualTo($this->due_at)) {
            return 'critical';
        }

        $diffMinutes = $now->diffInMinutes($this->due_at, false);
        if ($diffMinutes <= 60) {
            return 'critical';
        }

        if ($diffMinutes <= 180) {
            return 'warning';
        }

        return 'ontime';
    }

    /**
     * Formatea la hora de vencimiento (ej. 13:30 hrs).
     */
    public function getDueTimeFormattedAttribute(): string
    {
        return $this->due_at ? $this->due_at->format('H:i') . ' hrs' : '14:00 hrs';
    }
}
