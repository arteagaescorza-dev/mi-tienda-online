<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionType extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','meta'];
    protected $casts = ['meta' => 'array'];

    public function values()
    {
        return $this->hasMany(OptionValue::class);
    }
}
