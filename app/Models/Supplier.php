<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name','website','contact_email','type','credentials','notes'];
    protected $casts = ['credentials' => 'array'];

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
