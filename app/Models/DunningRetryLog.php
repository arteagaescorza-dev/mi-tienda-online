<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DunningRetryLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'was_recovered' => 'boolean',
        'scheduled_retry_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'subscription_id');
    }
}
