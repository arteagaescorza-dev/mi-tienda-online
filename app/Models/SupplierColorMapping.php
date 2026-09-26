<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierColorMapping extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'external_color', 'local_color'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}