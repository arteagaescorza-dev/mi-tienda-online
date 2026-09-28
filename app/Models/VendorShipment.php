<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorShipment extends Model
{
    use HasFactory;

    protected $table = 'vendor_shipments';

    protected $guarded = ['id'];

    protected $casts = [
        'pieces_dispatched' => 'integer',
        'dispatched_at'     => 'datetime',
        'delivered_at'      => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
