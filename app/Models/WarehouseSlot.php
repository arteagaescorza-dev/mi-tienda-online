<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseSlot extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_occupied' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'current_product_id');
    }
}
