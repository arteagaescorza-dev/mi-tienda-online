<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsOfficialDocument extends Model
{
    use HasFactory;

    protected $table = 'cms_official_documents';

    protected $fillable = [
        'code',
        'title',
        'payload',
        'version',
        'is_active',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getByCode(string $code, $default = null)
    {
        $doc = static::forDocument($code);
        return $doc ? $doc->payload : $default;
    }

    public static function forDocument(string $code): ?self
    {
        $upper = strtoupper(trim($code));
        $lower = strtolower(trim($code));
        $pluralUpper = $upper . 'S';
        $pluralLower = $lower . 's';
        $singularUpper = rtrim($upper, 'S');
        $singularLower = rtrim($lower, 's');

        return static::where(function ($q) use ($upper, $lower, $pluralUpper, $pluralLower, $singularUpper, $singularLower) {
            $q->whereIn('code', array_unique([$upper, $lower, $pluralUpper, $pluralLower, $singularUpper, $singularLower]));
        })->where('is_active', true)->first();
    }

    public static function getStandardCareSymbols(): array
    {
        return [
            'wash_30' => [
                'name' => 'Lavado frío (máx 30°C)',
                'category' => 'Lavado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h18l-2 10a3 3 0 01-3 3H8a3 3 0 01-3-3L3 8z"/><path d="M3 11c3 1 6-1 9 0s6-1 9 0"/></svg>',
            ],
            'wash_hand' => [
                'name' => 'Lavado a mano con jabón neutro',
                'category' => 'Lavado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h18l-2 10a3 3 0 01-3 3H8a3 3 0 01-3-3L3 8z"/><path d="M12 4v4m-2-3l2-2 2 2"/><path d="M3 12c3 1 6-1 9 0s6-1 9 0"/></svg>',
            ],
            'no_bleach' => [
                'name' => 'No usar cloro ni blanqueadores',
                'category' => 'Blanqueado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l10 17H2L12 3z"/><line x1="5" y1="18" x2="19" y2="6"/></svg>',
            ],
            'dry_flat' => [
                'name' => 'Secado en plano horizontal',
                'category' => 'Secado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="7" y1="12" x2="17" y2="12"/></svg>',
            ],
            'dry_shade' => [
                'name' => 'Secar a la sombra (sin sol directo)',
                'category' => 'Secado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="4" y1="8" x2="8" y2="4"/><line x1="4" y1="12" x2="12" y2="4"/></svg>',
            ],
            'no_tumble' => [
                'name' => 'No usar secadora térmica',
                'category' => 'Secado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="12" r="5"/><line x1="5" y1="19" x2="19" y2="5"/></svg>',
            ],
            'iron_low' => [
                'name' => 'Plancha suave (máx 110°C)',
                'category' => 'Planchado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17h18a1 1 0 001-1c0-4-3-8-8-8H4a1 1 0 00-1 1v8z"/><circle cx="10" cy="14" r="1" fill="currentColor"/></svg>',
            ],
            'no_iron' => [
                'name' => 'No planchar elásticos ni estampados',
                'category' => 'Planchado',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17h18a1 1 0 001-1c0-4-3-8-8-8H4a1 1 0 00-1 1v8z"/><line x1="4" y1="19" x2="20" y2="5"/></svg>',
            ],
            'no_dryclean' => [
                'name' => 'No lavar en seco',
                'category' => 'Limpieza profesional',
                'svg' => '<svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><line x1="6" y1="18" x2="18" y2="6"/></svg>',
            ],
        ];
    }
}
