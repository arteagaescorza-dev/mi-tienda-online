<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalTask extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'action_payload' => 'array',
    ];
}
