<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreeWayMatching extends Model
{
    use HasFactory;

    protected $table = 'three_way_matchings';

    protected $guarded = ['id'];

    protected $casts = [
        'po_qty'                 => 'integer',
        'po_total_amount'        => 'decimal:2',
        'warehouse_received_qty' => 'integer',
        'warehouse_damaged_qty'  => 'integer',
        'invoice_qty'            => 'integer',
        'invoice_gross_amount'   => 'decimal:2',
        'debit_note_amount'      => 'decimal:2',
        'payable_net_amount'     => 'decimal:2',
        'spei_payout_blocked'    => 'boolean',
    ];

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_code', 'po_number');
    }

    public function isBlocked(): bool
    {
        return (bool) $this->spei_payout_blocked;
    }

    public function isMatched(): bool
    {
        return $this->match_status === 'matched';
    }
}
