<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterdepartmentalMessage extends Model
{
    use HasFactory;

    protected $table = 'interdepartmental_messages';

    protected $guarded = ['id'];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = [
        'formatted_time',
        'tag_badge',
        'priority_badge_color',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Hora formateada para la visualización en el timeline del chat.
     */
    public function getFormattedTimeAttribute(): string
    {
        if (!$this->created_at) {
            return 'Justo ahora';
        }

        if ($this->created_at->isToday()) {
            return $this->created_at->format('h:i A');
        }

        if ($this->created_at->isYesterday()) {
            return 'Ayer ' . $this->created_at->format('h:i A');
        }

        return $this->created_at->format('d/m/Y h:i A');
    }

    /**
     * Etiqueta formateada del folio referenciado (@Pedido:..., @SKU:..., etc.).
     */
    public function getTagBadgeAttribute(): ?string
    {
        if (!$this->reference_code) {
            return null;
        }

        $typePrefix = match (strtolower($this->reference_type ?: '')) {
            'order', 'pedido' => '@Pedido:',
            'po', 'purchase_order', 'compra' => '@OC:',
            'sku', 'producto' => '@SKU:',
            'rma', 'devolucion' => '@RMA:',
            'shipment', 'guia', 'tracking' => '@Guia:',
            default => '@Folio:',
        };

        return $typePrefix . $this->reference_code;
    }

    /**
     * Color del indicador de prioridad.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'amber',
            default => 'slate',
        };
    }

    /**
     * Scope para filtrar por canal de comunicación.
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope para mensajes pendientes de resolución.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Marca el mensaje como resuelto con operador y marca temporal.
     */
    public function resolve(string $resolvedBy): bool
    {
        $this->is_resolved = true;
        $this->resolved_at = now();
        $this->resolved_by = $resolvedBy;
        return $this->save();
    }
}
