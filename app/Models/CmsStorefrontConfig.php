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
        'logo_url',
        'logo_mobile_url',
        'favicon_url',
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
        'border_color',
        'muted_text_color',
        'typography_heading',
        'typography_subtitle',
        'typography_body',
        'typography_accent',
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
        $universeDefaults = [
            'marte' => [
                'brand_name' => 'MARTE',
                'logo_url' => null,
                'logo_mobile_url' => null,
                'favicon_url' => null,
                'sub_title' => 'UNIVERSO MASCULINO',
                'hero_tag' => 'MARTE // COLECCIÓN OFICIAL TORZO',
                'hero_title' => 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA',
                'hero_subtitle' => 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.',
                'hero_cta_text' => 'VER COLECCIÓN',
                'hero_cta_url' => '/?universo=marte',
                'page_title' => 'MARTE — Universo Masculino & Multiverso Store',
                'primary_color' => '#09090b',
                'secondary_color' => '#27272a',
                'accent_color' => '#4f46e5',
                'background_color' => '#fafafa',
                'surface_color' => '#ffffff',
                'text_color' => '#09090b',
                'muted_text_color' => '#71717a',
                'border_color' => '#e4e4e7',
                'is_active' => true,
            ],
            'venus' => [
                'brand_name' => 'VENUS',
                'logo_url' => null,
                'logo_mobile_url' => null,
                'favicon_url' => null,
                'sub_title' => 'LENCERÍA & BEAUTY',
                'hero_tag' => 'VENUS // LENCERÍA DE AUTOR & SILUETAS',
                'hero_title' => 'LENCERÍA DE AUTOR & SILUETAS SENSORIALES',
                'hero_subtitle' => 'Encajes finos, satín italiano y fórmulas limpias de cuidado personal pensadas para tu bienestar diario.',
                'hero_cta_text' => 'VER COLECCIÓN',
                'hero_cta_url' => '/?universo=venus',
                'page_title' => 'VENUS — Lencería de Autor & Beauty',
                'primary_color' => '#1c1917',
                'secondary_color' => '#44403c',
                'accent_color' => '#db2777',
                'background_color' => '#fff1f2',
                'surface_color' => '#ffffff',
                'text_color' => '#1c1917',
                'muted_text_color' => '#78716c',
                'border_color' => '#fecdd3',
                'is_active' => true,
            ],
            'tiresias' => [
                'brand_name' => 'TIRESIAS',
                'logo_url' => null,
                'logo_mobile_url' => null,
                'favicon_url' => null,
                'sub_title' => 'MODA AFIRMATIVA',
                'hero_tag' => 'TIRESIAS // AFIRMATIVA & NO BINARIO',
                'hero_title' => 'LIBERTAD DE EXPRESIÓN Y AJUSTE SEGURO',
                'hero_subtitle' => 'Prendas afirmativas confeccionadas con ingeniería textil transpirable de compresión médica segura.',
                'hero_cta_text' => 'VER COLECCIÓN',
                'hero_cta_url' => '/?universo=tiresias',
                'page_title' => 'TIRESIAS — Moda Afirmativa & Binders',
                'primary_color' => '#0f172a',
                'secondary_color' => '#334155',
                'accent_color' => '#8b5cf6',
                'background_color' => '#faf5ff',
                'surface_color' => '#ffffff',
                'text_color' => '#0f172a',
                'muted_text_color' => '#64748b',
                'border_color' => '#e9d5ff',
                'is_active' => true,
            ],
            'terra' => [
                'brand_name' => 'TERRA',
                'logo_url' => null,
                'logo_mobile_url' => null,
                'favicon_url' => null,
                'sub_title' => 'ECOSISTEMA GLOBAL',
                'hero_tag' => 'TERRA // CATÁLOGO GLOBAL CONSOLIDADO',
                'hero_title' => 'EL ECOSISTEMA COMPLETO DE MODA ÍNTIMA Y ESTILO',
                'hero_subtitle' => 'Catálogo unificado, membresías mensuales recurrentes y fórmulas de autocuidado para todas las identidades.',
                'hero_cta_text' => 'VER CATÁLOGO GLOBAL',
                'hero_cta_url' => '/?universo=terra',
                'page_title' => 'TERRA — Ecosistema Multiverso Store',
                'primary_color' => '#09090b',
                'secondary_color' => '#27272a',
                'accent_color' => '#0284c7',
                'background_color' => '#fafafa',
                'surface_color' => '#ffffff',
                'text_color' => '#09090b',
                'muted_text_color' => '#71717a',
                'border_color' => '#e4e4e7',
                'is_active' => true,
            ],
        ];

        return static::firstOrCreate(
            ['universe' => $normalized],
            $universeDefaults[$normalized] ?? $universeDefaults['marte']
        );
    }
}
