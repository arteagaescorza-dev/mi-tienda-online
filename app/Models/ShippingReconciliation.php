<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingReconciliation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'invoice_date' => 'date',
        'total_declared_amount' => 'decimal:2',
        'total_invoiced_amount' => 'decimal:2',
        'total_expected_amount' => 'decimal:2',
        'total_discrepancy_amount' => 'decimal:2',
        'total_overcharge_amount' => 'decimal:2',
        'total_recovered_amount' => 'decimal:2',
        'total_tracks_count' => 'integer',
        'matched_tracks_count' => 'integer',
        'discrepant_tracks_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->reconciliation_folio)) {
                $model->reconciliation_folio = $model->reconciliation_code ?? ('REC-' . strtoupper(uniqid()));
            }
            if (empty($model->reconciliation_code)) {
                $model->reconciliation_code = $model->reconciliation_folio;
            }
            if (empty($model->invoice_number)) {
                $model->invoice_number = $model->invoice_folio ?? ('INV-' . strtoupper(uniqid()));
            }
            if (empty($model->invoice_folio)) {
                $model->invoice_folio = $model->invoice_number;
            }
            if (empty($model->carrier) && !empty($model->carrier_id)) {
                $model->carrier = $model->carrier_id;
            }
            if (empty($model->carrier_id) && !empty($model->carrier)) {
                $model->carrier_id = $model->carrier;
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShippingReconciliationItem::class, 'reconciliation_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ShippingDiscrepancyClaim::class, 'reconciliation_id');
    }

    public function getFolioAttribute(): string
    {
        return $this->reconciliation_code ?? $this->reconciliation_folio ?? ('RECON-' . $this->id);
    }

    public function getCarrierLabelAttribute(): string
    {
        return $this->carrier_name ?? strtoupper($this->carrier ?? $this->carrier_id ?? 'COURIER');
    }
}
