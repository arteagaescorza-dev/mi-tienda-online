<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierCreditAgreement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'unit_cost_applied' => 'decimal:2',
        'missing_units_count' => 'integer',
        'damaged_units_count' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function appliedToPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'applied_to_po_id');
    }
}
