<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApparelSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'size_curve_id',
        'code',
        'label',
        'sort_order',
        'waist_min_cm',
        'waist_max_cm',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'waist_min_cm' => 'decimal:2',
        'waist_max_cm' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function curve(): BelongsTo
    {
        return $this->belongsTo(ApparelSizeCurve::class, 'size_curve_id');
    }
}
