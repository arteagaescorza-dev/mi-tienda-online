<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id','sku','attributes','price','cost','visible'];

    protected $hidden = ['cost'];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'visible' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(OptionValue::class, 'product_variant_option', 'variant_id', 'option_value_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'variant_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'variant_id')->where('location', 'warehouse-1');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }
}
