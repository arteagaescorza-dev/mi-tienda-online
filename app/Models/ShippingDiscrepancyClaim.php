<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingDiscrepancyClaim extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'claimed_amount' => 'decimal:2',
        'total_disputed_amount' => 'decimal:2',
        'disputed_items_count' => 'integer',
        'submitted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(ShippingReconciliation::class, 'reconciliation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShippingReconciliationItem::class, 'shipping_discrepancy_claim_id');
    }
}
