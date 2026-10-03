<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'vendor_code',
        'tax_id',
        'fiscal_regime',
        'fiscal_address',
        'fiscal_zip',
        'website',
        'contact_name',
        'contact_email',
        'invoice_email',
        'phone',
        'city',
        'type',
        'origin',
        'lead_time_days',
        'payment_terms',
        'status',
        'extraction_method',
        'scrape_frequency',
        'sla_on_time_pct',
        'sla_fill_rate_pct',
        'avg_delay_days',
        'credentials',
        'brands',
        'notes',
    ];

    protected $casts = [
        'credentials' => 'array',
        'brands' => 'array',
        'lead_time_days' => 'integer',
        'sla_on_time_pct' => 'float',
        'sla_fill_rate_pct' => 'float',
        'avg_delay_days' => 'float',
    ];

    /**
     * Lista canónica de términos reconocidos como Líneas de Producto / Tipos de Prenda (Categorías),
     * los cuales NUNCA deben tratarse como Marcas Comerciales (Regla de separación de Marcas vs Líneas).
     */
    public const PRODUCT_LINE_CATEGORIES = [
        'tanga', 'tangas',
        'bikini', 'bikinis',
        'jockstraps', 'jockstrap', 'jocks',
        'brief/boxers', 'briefs', 'boxers', 'boxer', 'brief',
        'tank tops', 'tank top',
        't-shirts', 't-shirt', 'camisillas', 'camisilla', 'playera', 'playeras',
        'swimwear', 'trajes de bano', 'trajes de baño',
        'combos', 'combo',
        'accesorios', 'accesorio',
        'skincare', 'perfumeria', 'perfumería', 'lenceria', 'lencería',
    ];

    public static function isProductLineCategory(?string $name): bool
    {
        if (empty($name)) {
            return false;
        }
        $normalized = mb_strtolower(trim($name), 'UTF-8');
        return in_array($normalized, self::PRODUCT_LINE_CATEGORIES, true);
    }

    /**
     * Accesor para trade_name que mapea al nombre comercial (columna name).
     */
    public function getTradeNameAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Obtiene el listado de marcas comerciales que se desprenden del proveedor en formato de arbol.
     * Si no estan explicitamente definidas en la columna brands, se deducen dinamicamente
     * de los productos vinculados o del nombre comercial, EXCLUYENDO rigurosamente tipos de prenda/líneas.
     *
     * @return array<int, array{name: string, slug: string, origin: string, is_active: bool}>
     */
    public function getBrandsTree(): array
    {
        $explicitBrands = array_filter(
            is_array($this->brands) ? $this->brands : [],
            fn($b) => !empty($b) && !self::isProductLineCategory($b)
        );

        $dbBrands = [];
        if ($this->relationLoaded('brandsList')) {
            $dbBrands = $this->brandsList->pluck('name')
                ->filter(fn($b) => !empty($b) && !self::isProductLineCategory($b))
                ->toArray();
        } elseif ($this->id) {
            $dbBrands = Brand::where('supplier_id', $this->id)->pluck('name')
                ->filter(fn($b) => !empty($b) && !self::isProductLineCategory($b))
                ->toArray();
        }

        // Deduccion automatica de marcas desde productos asociados si esta cargada la relacion o existe
        $productBrands = [];
        if ($this->relationLoaded('supplierProducts')) {
            $productBrands = $this->supplierProducts
                ->pluck('product.brand')
                ->filter(fn($b) => !empty($b) && !self::isProductLineCategory($b))
                ->unique()
                ->values()
                ->toArray();
        }

        $allBrandNames = array_values(array_unique(array_filter(array_merge($explicitBrands, $dbBrands, $productBrands))));

        if (empty($allBrandNames)) {
            $allBrandNames = [!self::isProductLineCategory($this->name) ? $this->name : 'General'];
        }

        return array_map(function (string $brandName) {
            return [
                'name' => $brandName,
                'slug' => \Illuminate\Support\Str::slug($brandName),
                'origin' => $this->origin ?? ($this->type === 'dropship_scrape' ? 'scraping' : 'manual'),
                'is_active' => $this->status !== 'archived',
            ];
        }, $allBrandNames);
    }

    public function brandsList()
    {
        return $this->hasMany(Brand::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function syncRuns()
    {
        return $this->hasMany(SupplierSyncRun::class);
    }

    public function colorMappings()
    {
        return $this->hasMany(SupplierColorMapping::class);
    }

    public function sizeCurves()
    {
        return $this->hasMany(ApparelSizeCurve::class);
    }

    public function moqSheets()
    {
        return $this->hasMany(SupplierMoqSheet::class, 'supplier_id');
    }
}
