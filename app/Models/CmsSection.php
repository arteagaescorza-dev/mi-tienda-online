<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    protected $appends = [
        'slug',
        'description',
        'is_active',
        'is_archived',
    ];

    public function getSlugAttribute(): string
    {
        if (!empty($this->settings['slug'])) {
            return (string) $this->settings['slug'];
        }
        if (!empty($this->query_rules['slug'])) {
            return (string) $this->query_rules['slug'];
        }

        $map = [
            'línea básicos esenciales' => 'basicos',
            'linea basicos esenciales' => 'basicos',
            'línea diseñador & autor' => 'disenador',
            'linea diseñador & autor' => 'disenador',
            'linea diseñador y autor' => 'disenador',
            'colección temporada / drops' => 'temporada',
            'coleccion temporada / drops' => 'temporada',
            'catálogo general' => 'general',
            'catalogo general' => 'general',
            'hero editorial principal' => 'hero-editorial',
            'pilares de marca & courier' => 'pilares-marca',
            'convenios & aliados multiverso' => 'convenios-aliados',
            'prendas destacadas del catálogo' => 'prendas-destacadas',
            'prendas destacadas del catalogo' => 'prendas-destacadas',
        ];

        $lower = mb_strtolower(trim($this->name ?? ''));
        if (isset($map[$lower])) {
            return $map[$lower];
        }

        return Str::slug($this->name ?: ('section-' . $this->id));
    }

    public function getDescriptionAttribute(): string
    {
        return (string) ($this->subtitle ?: ($this->settings['description'] ?? ''));
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->status === 'archived';
    }

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
