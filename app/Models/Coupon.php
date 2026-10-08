<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

        protected $fillable = [
        'code', 'discount_type', 'discount_value', 'min_order_amount', 'target_universe', 'max_uses', 'used_count', 'is_active', 'expires_at', 'revenue_generated'
    ];

    protected $attributes = [
        'is_active' => true,
        'used_count' => 0,
        'revenue_generated' => 0.00,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
