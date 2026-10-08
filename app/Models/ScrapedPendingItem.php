<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapedPendingItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'sizes' => 'array',
        'images' => 'array',
        'raw_snapshot' => 'array',
        'external_price' => 'decimal:2',
        'scraped_cost' => 'decimal:2',
        'curated_price' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
