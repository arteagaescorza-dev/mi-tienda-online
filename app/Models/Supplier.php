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
     * Accesor para trade_name que mapea al nombre comercial (columna name).
     */
    public function getTradeNameAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Obtiene el listado de marcas comerciales que se desprenden del proveedor en formato de arbol.
     * Si no estan explicitamente definidas en la columna brands, se deducen dinamicamente
     * de los productos vinculados o del nombre comercial.
     *
     * @return array<int, array{name: string, sku_count: int, origin: string}>
     */
    public function getBrandsTree(): array
    {
        $explicitBrands = is_array($this->brands) ? $this->brands : [];

        $dbBrands = [];
        if ($this->relationLoaded('brandsList')) {
            $dbBrands = $this->brandsList->pluck('name')->toArray();
        } elseif ($this->id) {
            $dbBrands = Brand::where('supplier_id', $this->id)->pluck('name')->toArray();
        }

        // Deduccion automatica de marcas desde productos asociados si esta cargada la relacion o existe
        $productBrands = [];
        if ($this->relationLoaded('supplierProducts')) {
            $productBrands = $this->supplierProducts
                ->pluck('product.brand')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        }

        $allBrandNames = array_values(array_unique(array_filter(array_merge($explicitBrands, $dbBrands, $productBrands))));

        if (empty($allBrandNames)) {
            $allBrandNames = [$this->name];
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
}
