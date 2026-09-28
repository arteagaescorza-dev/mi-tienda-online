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
        $doc = static::where('code', $code)->where('is_active', true)->first();
        return $doc ? $doc->payload : $default;
    }
}
