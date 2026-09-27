<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'packaging_fulfilled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function manifest(): BelongsTo
    {
        return $this->belongsTo(ShippingManifest::class, 'manifest_id');
    }

    public function packageType(): BelongsTo
    {
        return $this->belongsTo(PackageType::class, 'package_type_id');
    }

    public function packagingMovements()
    {
        return $this->hasMany(PackagingMovement::class);
    }

    public function getWeightKgAttribute(): float
    {
        return (float)($this->attributes['weight_kg'] ?? $this->weight_actual_kg ?? 0.50);
    }
}
