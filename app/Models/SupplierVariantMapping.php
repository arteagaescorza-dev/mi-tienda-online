<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierVariantMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_product_id', 'product_variant_id', 'supplier_variant_key', 'attributes', 'stock', 'available',
    ];

    protected $casts = [
        'attributes' => 'array',
        'available' => 'boolean',
    ];

    public function supplierProduct()
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}