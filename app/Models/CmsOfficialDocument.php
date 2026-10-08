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
        return static::where(function ($q) use ($upper, $lower) {
            $q->where('code', $upper)->orWhere('code', $lower);
        })->where('is_active', true)->first();
    }
}
