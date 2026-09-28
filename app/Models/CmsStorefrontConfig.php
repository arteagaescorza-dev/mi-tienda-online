<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsStorefrontConfig extends Model
{
    use HasFactory;

    protected $table = 'cms_storefront_configs';

    protected $fillable = [
        'universe',
        'brand_name',
        'sub_title',
        'kicker',
        'hero_tag',
        'hero_title',
        'hero_subtitle',
        'hero_cta_text',
        'hero_cta_url',
        'page_title',
        'primary_color',
        'secondary_color',
        'accent_color',
        'background_color',
        'surface_color',
        'text_color',
        'typography_heading',
        'typography_body',
        'custom_css',
        'custom_js',
        'seo_meta',
        'is_active',
    ];

    protected $casts = [
        'seo_meta' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get or create config for a specific universe with fallback defaults.
     */
    public static function forUniverse(string $universe): self
    {
        $normalized = strtolower(trim($universe));
        if (!in_array($normalized, ['marte', 'venus', 'tiresias', 'terra'])) {
            $normalized = 'marte';
        }

        return static::firstOrCreate(
            ['universe' => $normalized],
            [
                'brand_name' => strtoupper($normalized),
                'sub_title' => 'UNIVERSO ' . strtoupper($normalized),
                'hero_tag' => strtoupper($normalized) . ' // COLECCIÓN OFICIAL TORZO',
                'hero_title' => 'DISEÑO, COMODIDAD Y FIRMEZA SIN CENSURA',
                'hero_subtitle' => 'Textiles premium de modal y bambú diseñados para la máxima libertad de movimiento y soporte atlético.',
                'hero_cta_text' => 'VER COLECCIÓN',
                'hero_cta_url' => '/productos?universo=' . $normalized,
                'page_title' => strtoupper($normalized) . ' — Tienda Oficial',
                'primary_color' => $normalized === 'venus' ? '#1c1917' : ($normalized === 'tiresias' ? '#0f172a' : '#09090b'),
                'accent_color' => $normalized === 'venus' ? '#db2777' : ($normalized === 'tiresias' ? '#8b5cf6' : ($normalized === 'terra' ? '#0284c7' : '#4f46e5')),
                'background_color' => $normalized === 'venus' ? '#fff1f2' : ($normalized === 'tiresias' ? '#faf5ff' : '#fafafa'),
                'surface_color' => '#ffffff',
                'text_color' => '#0f172a',
                'is_active' => true,
            ]
        );
    }
}
