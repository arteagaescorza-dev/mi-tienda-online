<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ally extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function campaignsDirA(): HasMany
    {
        return $this->hasMany(AllyCampaignDirA::class);
    }

    public function leadCaptures(): HasMany
    {
        return $this->hasMany(AllyLeadCapture::class);
    }

    public function couponsDirB(): HasMany
    {
        return $this->hasMany(AllyCouponDirB::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(AllyClearingSettlement::class);
    }
}
