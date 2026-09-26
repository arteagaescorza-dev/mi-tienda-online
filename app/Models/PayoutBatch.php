<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutBatch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'deposit_date' => 'datetime',
    ];
}
