<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','variant_id','quantity','unit_price','line_total','supplier_id','fulfillment_status'];
    protected $casts = ['unit_price' => 'decimal:2','line_total' => 'decimal:2'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
