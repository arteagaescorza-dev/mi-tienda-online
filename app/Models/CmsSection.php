<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsSection extends Model
{
    use HasFactory;

    protected $table = 'cms_sections';

    protected $fillable = [
        'universe',
        'name',
        'type',
        'title',
        'subtitle',
        'badge',
        'query_rules',
        'custom_html',
        'custom_css',
        'custom_js',
        'settings',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'query_rules' => 'array',
        'settings' => 'array',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUniverse($query, string $universe)
    {
        return $query->where(function ($q) use ($universe) {
            $q->where('universe', 'all')
              ->orWhere('universe', strtolower($universe));
        });
    }
}
