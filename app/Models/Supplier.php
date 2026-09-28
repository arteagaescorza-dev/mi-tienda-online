<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vendor_code',
        'website',
        'contact_name',
        'contact_email',
        'phone',
        'city',
        'type',
        'lead_time_days',
        'payment_terms',
        'status',
        'extraction_method',
        'sla_on_time_pct',
        'sla_fill_rate_pct',
        'avg_delay_days',
        'credentials',
        'notes',
    ];

    protected $casts = [
        'credentials' => 'array',
        'lead_time_days' => 'integer',
        'sla_on_time_pct' => 'float',
        'sla_fill_rate_pct' => 'float',
        'avg_delay_days' => 'float',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function syncRuns()
    {
        return $this->hasMany(SupplierSyncRun::class);
    }

    public function colorMappings()
    {
        return $this->hasMany(SupplierColorMapping::class);
    }
}
