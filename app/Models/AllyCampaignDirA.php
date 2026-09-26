<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllyCampaignDirA extends Model
{
    use HasFactory;

    protected $table = 'ally_campaigns_dir_a';
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function leadCaptures(): HasMany
    {
        return $this->hasMany(AllyLeadCapture::class, 'campaign_id');
    }
}
