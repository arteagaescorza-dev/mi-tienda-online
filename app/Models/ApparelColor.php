<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApparelColor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['hex', 'type'];

    public function getHexAttribute(): string
    {
        return $this->gradient_css ?: ($this->hex_code ?: '#000000');
    }

    public function getTypeAttribute(): string
    {
        return in_array($this->color_type, ['gradient', 'iridescent']) ? 'iridescent' : 'solid';
    }
}
