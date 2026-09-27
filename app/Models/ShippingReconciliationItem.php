<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingReconciliationItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'declared_weight_kg' => 'decimal:2',
        'invoiced_weight_kg' => 'decimal:2',
        'declared_cost' => 'decimal:2',
        'invoiced_cost' => 'decimal:2',
        'difference_cost' => 'decimal:2',
        'warehouse_weight_kg' => 'decimal:2',
        'warehouse_volumetric_kg' => 'decimal:2',
        'carrier_billed_weight_kg' => 'decimal:2',
        'expected_rate_mxn' => 'decimal:2',
        'carrier_invoiced_rate_mxn' => 'decimal:2',
        'overcharge_amount_mxn' => 'decimal:2',
        'overcharge_pct' => 'decimal:2',
        'is_overcharged' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->reconciliation_id) && !empty($model->shipping_reconciliation_id)) {
                $model->reconciliation_id = $model->shipping_reconciliation_id;
            }
            if (empty($model->shipping_reconciliation_id) && !empty($model->reconciliation_id)) {
                $model->shipping_reconciliation_id = $model->reconciliation_id;
            }
            if (empty($model->declared_weight_kg)) {
                $model->declared_weight_kg = $model->warehouse_weight_kg ?? 0.0;
            }
            if (empty($model->invoiced_weight_kg)) {
                $model->invoiced_weight_kg = $model->carrier_billed_weight_kg ?? 0.0;
            }
            if (empty($model->declared_cost)) {
                $model->declared_cost = $model->expected_rate_mxn ?? 0.0;
            }
            if (empty($model->invoiced_cost)) {
                $model->invoiced_cost = $model->carrier_invoiced_rate_mxn ?? 0.0;
            }
            if (empty($model->difference_cost)) {
                $model->difference_cost = $model->overcharge_amount_mxn ?? 0.0;
            }
        });
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(ShippingReconciliation::class, 'reconciliation_id');
    }

    public function manifest(): BelongsTo
    {
        return $this->belongsTo(ShippingManifest::class, 'shipping_manifest_id');
    }

    public function packageType(): BelongsTo
    {
        return $this->belongsTo(PackageType::class, 'package_type_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ShippingDiscrepancyClaim::class, 'shipping_discrepancy_claim_id');
    }

    public function isOvercharged(): bool
    {
        return $this->is_overcharged || $this->overcharge_amount_mxn > 0 || $this->audit_status === 'discrepancy';
    }
}
