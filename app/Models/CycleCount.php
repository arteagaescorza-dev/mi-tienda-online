<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CycleCount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'count_date' => 'date',
        'details_json' => 'array',
    ];
}
