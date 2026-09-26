<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllyClearingSettlement extends Model
{
    use HasFactory;

    protected $table = 'ally_clearing_settlements';
    protected $guarded = ['id'];

    protected $casts = [
        'settled_at' => 'datetime',
    ];

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }
}
