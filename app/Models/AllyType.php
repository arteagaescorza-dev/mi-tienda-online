<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllyType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function isArchived(): bool
    {
        return $this->status === 'archived' || $this->trashed();
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active' && !$this->trashed();
    }

    public function allies(): HasMany
    {
        return $this->hasMany(Ally::class, 'category', 'code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }
}
