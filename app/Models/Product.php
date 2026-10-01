<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['sku','title','slug','description','brand','visible','type','price','cost','weight','meta'];

    protected $casts = [
        'meta' => 'array',
        'visible' => 'boolean',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public static function sanitizeUtf8(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        // Detectar y corregir secuencias de doble codificación UTF-8 / Mojibake
        if (preg_match('/(Ã¡|Ã©|Ã­|Ã³|Ãº|Ã±|Ã |Ã‰|Ã |Ã“|Ãš|Ã‘|Â¿|Â¡|Ã¼|Ãœ|â€|â\x80|Â)/u', $text)) {
            $decoded = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $text);
            if ($decoded !== false && mb_check_encoding($decoded, 'UTF-8')) {
                return $decoded;
            }
        }

        return $text;
    }

    public function getDescriptionAttribute(?string $value): ?string
    {
        return static::sanitizeUtf8($value);
    }

    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = static::sanitizeUtf8($value);
    }

    public function getTitleAttribute(?string $value): ?string
    {
        return static::sanitizeUtf8($value);
    }

    public function setTitleAttribute(?string $value): void
    {
        $this->attributes['title'] = static::sanitizeUtf8($value);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }
}
