<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionValue extends Model
{
    use HasFactory;

    protected $fillable = ['option_type_id','value','label','meta'];
    protected $casts = ['meta' => 'array'];

    public function type()
    {
        return $this->belongsTo(OptionType::class, 'option_type_id');
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_option', 'option_value_id', 'variant_id');
    }
}
