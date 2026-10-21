<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ally extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'allowed_discount_types' => 'array',
        'max_discount_pct' => 'float',
        'commission_rate' => 'float',
        'lead_acquisition_cost' => 'float',
        'clearing_balance' => 'float',
    ];

    public function isArchived(): bool
    {
        return in_array($this->status, ['archived', 'paused', 'terminated'], true) || $this->trashed();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->trashed();
    }

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
