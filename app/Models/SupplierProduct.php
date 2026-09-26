<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id','supplier_sku','product_id','supplier_url','last_checked_at','price','availability','raw_snapshot'];
    protected $casts = ['raw_snapshot' => 'array','last_checked_at' => 'datetime','price' => 'decimal:2'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variantMappings()
    {
        return $this->hasMany(SupplierVariantMapping::class);
    }
}
