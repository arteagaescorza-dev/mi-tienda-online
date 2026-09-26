<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribution extends Model
{
    use HasFactory;

    protected $fillable = ['session_id','source','medium','campaign','term','content','landing_url','user_id','first_seen_at','last_activity_at'];
    protected $casts = ['first_seen_at' => 'datetime','last_activity_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'attributed_via_id');
    }
}
