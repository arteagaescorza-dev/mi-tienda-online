<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmaResolution extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'store_credit_amount' => 'decimal:2',
        'whatsapp_message_sent' => 'boolean',
        'whatsapp_payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rmaRequest(): BelongsTo
    {
        return $this->belongsTo(RmaRequest::class, 'rma_request_id');
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
