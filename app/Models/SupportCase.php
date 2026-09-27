<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportCase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];
}
