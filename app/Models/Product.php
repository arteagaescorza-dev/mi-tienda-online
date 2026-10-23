<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'title',
        'slug',
        'description',
        'brand',
        'fabric',
        'fabric_id',
        'section_name',
        'visible',
        'is_archived',
        'archived_at',
        'discontinued_reason',
        'type',
        'price',
        'cost',
        'weight',
        'meta',
    ];

    protected $hidden = [
        'cost',
    ];

    protected $casts = [
        'meta' => 'array',
        'visible' => 'boolean',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
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

    public function getImagesAttribute(): array
    {
        $meta = $this->meta ?? [];
        if (!empty($meta['images']) && is_array($meta['images'])) {
            return $meta['images'];
        }
        if (!empty($meta['gallery']) && is_array($meta['gallery'])) {
            return array_column($meta['gallery'], 'url');
        }
        if (!empty($meta['image'])) {
            return [$meta['image']];
        }
        return [];
    }

    public function getImagesByColorAttribute(): array
    {
        $meta = $this->meta ?? [];
        return $meta['images_by_color'] ?? ($meta['image_color_map'] ?? []);
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

    public function fabric()
    {
        return $this->belongsTo(ApparelFabric::class, 'fabric_id');
    }

    public function fabricRelation()
    {
        return $this->belongsTo(ApparelFabric::class, 'fabric_id');
    }

    /**
     * Determina si el producto proviene de scraping o dropshipping externo (bajo pedido).
     * Política Oficial: Todo producto scrapeado o dropshipping opera exclusivamente bajo pedido con 0 stock local.
     */
    public function isScraped(): bool
    {
        if (in_array($this->type, ['dropship', 'scraped'], true)) {
            return true;
        }

        if ((bool) data_get($this->meta, 'is_scraped')) {
            return true;
        }

        $provider = strtolower(trim((string) data_get($this->meta, 'provider', data_get($this->meta, 'supplier_name', ''))));
        if (str_contains($provider, 'torzo') || str_contains($provider, 'adrenaline')) {
            return true;
        }

        if ($this->relationLoaded('supplierProducts')) {
            if ($this->supplierProducts->contains(function ($sp) {
                return $sp->supplier && in_array($sp->supplier->type, ['dropship_scrape', 'dropship_api'], true);
            })) {
                return true;
            }
        } elseif ($this->supplierProducts()->whereHas('supplier', function ($q) {
            $q->whereIn('type', ['dropship_scrape', 'dropship_api']);
        })->exists()) {
            return true;
        }

        if ($this->type === 'physical') {
            return false;
        }

        return false;
    }
}
