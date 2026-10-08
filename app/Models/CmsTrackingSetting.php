<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsTrackingSetting extends Model
{
    use HasFactory;

    protected $table = 'cms_tracking_settings';

    protected $fillable = [
        'gtm_id',
        'ga4_measurement_id',
        'facebook_pixel_id',
        'facebook_capi_token',
        'facebook_test_event_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'gtm_id' => null,
            'ga4_measurement_id' => null,
            'facebook_pixel_id' => null,
            'is_active' => false,
        ]);
    }
}
