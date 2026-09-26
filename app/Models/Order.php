<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_number','user_id','billing_address','shipping_address','status','total','payment_status','payment_method','notes','created_via','attributed_via_id'];
    protected $casts = ['billing_address' => 'array','shipping_address' => 'array','total' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attribution()
    {
        return $this->belongsTo(Attribution::class, 'attributed_via_id');
    }
}
