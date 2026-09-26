<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_PROPOSAL = 'proposal';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';

    public const STATUS_LABELS = [
        self::STATUS_NEW => 'Nuevo',
        self::STATUS_CONTACTED => 'Contactado',
        self::STATUS_QUALIFIED => 'Calificado',
        self::STATUS_PROPOSAL => 'Propuesta enviada',
        self::STATUS_WON => 'Ganado',
        self::STATUS_LOST => 'Perdido',
    ];

    public const STATUS_CLASSES = [
        self::STATUS_NEW => 'status-new',
        self::STATUS_CONTACTED => 'status-contacted',
        self::STATUS_QUALIFIED => 'status-qualified',
        self::STATUS_PROPOSAL => 'status-proposal',
        self::STATUS_WON => 'status-won',
        self::STATUS_LOST => 'status-lost',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'source', 'status', 'notes', 'metadata', 'last_contacted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_contacted_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusClassAttribute(): string
    {
        return self::STATUS_CLASSES[$this->status] ?? 'status-new';
    }

    public function interactions()
    {
        return $this->hasMany(LeadInteraction::class);
    }

    public function conversations()
    {
        return $this->hasMany(ChatbotConversation::class);
    }
}
