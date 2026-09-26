<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingManifest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'manifest_id');
    }
}
