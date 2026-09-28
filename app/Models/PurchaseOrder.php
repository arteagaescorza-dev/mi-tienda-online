<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'sent_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'received_at' => 'datetime',
        'po_snapshot' => 'array',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gross_subtotal' => 'decimal:2',
        'credit_deduction_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creditAgreement(): BelongsTo
    {
        return $this->belongsTo(SupplierCreditAgreement::class, 'credit_agreement_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function kardex(): HasMany
    {
        return $this->hasMany(PurchaseKardex::class);
    }

    public function vendorShipments(): HasMany
    {
        return $this->hasMany(VendorShipment::class, 'po_id');
    }

    public function latestVendorShipment()
    {
        return $this->hasOne(VendorShipment::class, 'po_id')->latestOfMany();
    }
}

