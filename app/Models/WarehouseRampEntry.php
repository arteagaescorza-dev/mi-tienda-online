<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseRampEntry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'docked_at' => 'datetime',
        'cleared_at' => 'datetime',
    ];
}
