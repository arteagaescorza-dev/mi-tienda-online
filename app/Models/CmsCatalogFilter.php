<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsCatalogFilter extends Model
{
    use HasFactory;

    protected $table = 'cms_catalog_filters';

    protected $fillable = [
        'filter_key',
        'label',
        'display_type',
        'sort_order',
        'is_visible',
        'config',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'config' => 'array',
    ];

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true)->orderBy('sort_order');
    }
}
