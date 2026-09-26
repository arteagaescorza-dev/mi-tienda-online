<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllyCouponDirB extends Model
{
    use HasFactory;

    protected $table = 'ally_coupons_dir_b';
    protected $guarded = ['id'];

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }
}
