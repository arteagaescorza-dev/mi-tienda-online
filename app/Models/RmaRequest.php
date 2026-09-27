<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RmaRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'refund_or_credit_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function inspectionLogs(): HasMany
    {
        return $this->hasMany(RmaInspectionLog::class, 'rma_id');
    }

    public function resolutions(): HasMany
    {
        return $this->hasMany(RmaResolution::class, 'rma_request_id');
    }

    public function resolution(): HasOne
    {
        return $this->hasOne(RmaResolution::class, 'rma_request_id')->latestOfMany();
    }

    public function replacementOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'replacement_order_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
