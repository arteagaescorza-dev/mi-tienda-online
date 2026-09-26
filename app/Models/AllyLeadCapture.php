<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllyLeadCapture extends Model
{
    use HasFactory;

    protected $table = 'ally_lead_captures';
    protected $guarded = ['id'];

    protected $casts = [
        'is_redeemed' => 'boolean',
        'redeemed_at' => 'datetime',
    ];

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AllyCampaignDirA::class, 'campaign_id');
    }
}
