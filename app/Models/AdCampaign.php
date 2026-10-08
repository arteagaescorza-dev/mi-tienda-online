<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCampaign extends Model
{
    use HasFactory;

        protected $fillable = [
        'name', 'universe', 'status', 'daily_budget', 'spend_today', 'platform_roas', 'net_real_roas', 'cpa', 'ctr', 'fatigue_alert', 'campaign_id_external'
    ];

    protected $casts = [
        'fatigue_alert' => 'boolean',
    ];
}
