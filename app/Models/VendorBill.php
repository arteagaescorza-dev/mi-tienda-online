<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_spei_blocked' => 'boolean',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function threeWayMatching(): BelongsTo
    {
        return $this->belongsTo(ThreeWayMatching::class, 'three_way_matching_id');
    }
}
