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
}
