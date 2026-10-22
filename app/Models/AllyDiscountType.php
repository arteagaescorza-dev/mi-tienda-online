<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllyDiscountType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'max_value_default' => 'float',
    ];

    public function isArchived(): bool
    {
        return $this->status === 'archived' || $this->trashed();
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active' && !$this->trashed();
    }

    public function getDirectionLabelAttribute(): string
    {
        return match ($this->direction) {
            'dir_a' => 'Dirección A (Evento / Local del Aliado)',
            'dir_b' => 'Dirección B (Tienda Online)',
            'both' => 'Ambas Direcciones (A & B)',
            default => ucfirst(str_replace('_', ' ', $this->direction)),
        };
    }

    public function getValueTypeLabelAttribute(): string
    {
        return match ($this->value_type) {
            'percentage' => '% Porcentaje de Descuento',
            'fixed_amount' => '$ Monto Fijo en Moneda',
            'free_item' => 'Cortesía / Ítem o Trago Libre',
            'access' => 'Acceso Preferente / VIP',
            default => ucfirst($this->value_type),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeDirA($query)
    {
        return $query->whereIn('direction', ['dir_a', 'both']);
    }

    public function scopeDirB($query)
    {
        return $query->whereIn('direction', ['dir_b', 'both']);
    }
}

