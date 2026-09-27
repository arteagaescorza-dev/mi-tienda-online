<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApparelSizeCurve extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category_scope',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sizes(): HasMany
    {
        return $this->hasMany(ApparelSize::class, 'size_curve_id')->orderBy('sort_order');
    }

    public function activeSizes(): HasMany
    {
        return $this->sizes()->where('is_active', true);
    }
}
