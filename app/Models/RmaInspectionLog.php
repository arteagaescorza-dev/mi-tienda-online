<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmaInspectionLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'has_hygiene_seal_intact' => 'boolean',
        'has_factory_tag' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function rma(): BelongsTo
    {
        return $this->belongsTo(RmaRequest::class, 'rma_id');
    }
}
