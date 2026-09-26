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
