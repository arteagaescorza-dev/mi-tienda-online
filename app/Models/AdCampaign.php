<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCampaign extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'fatigue_alert' => 'boolean',
    ];
}
