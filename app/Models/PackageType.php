<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'reorder_point' => 'integer',
        'stock_available' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function packagingMovements(): HasMany
    {
        return $this->hasMany(PackagingMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_available <= ($this->reorder_point ?? 40);
    }
}
