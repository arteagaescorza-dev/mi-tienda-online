<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    use HasFactory;

    protected $table = 'cms_pages';

    protected $fillable = [
        'slug',
        'title',
        'icon',
        'image_url',
        'overlay_gradient',
        'content_html',
        'show_in_navbar',
        'show_in_mobile',
        'filter_universe',
        'filter_category',
        'filter_tags',
        'navbar_order',
        'show_in_footer',
        'footer_order',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'status',
    ];

    protected $casts = [
        'show_in_navbar' => 'boolean',
        'show_in_mobile' => 'boolean',
        'show_in_footer' => 'boolean',
        'navbar_order' => 'integer',
        'footer_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInNavbar($query)
    {
        return $query->active()
            ->where('show_in_navbar', true)
            ->orderBy('navbar_order');
    }

    public function scopeInMobileNavbar($query)
    {
        return $query->active()
            ->where('show_in_mobile', true)
            ->orderBy('navbar_order');
    }

    public function scopeInFooter($query)
    {
        return $query->active()
            ->where('show_in_footer', true)
            ->orderBy('footer_order');
    }

    public function getOverlayGradientCss(): string
    {
        $val = trim($this->overlay_gradient ?? 'dark');

        if (str_starts_with($val, 'linear-gradient') || str_starts_with($val, 'radial-gradient')) {
            return $val;
        }

        return match ($val) {
            'indigo' => 'linear-gradient(180deg, rgba(30, 27, 75, 0.3) 0%, rgba(49, 46, 129, 0.88) 100%)',
            'rose' => 'linear-gradient(180deg, rgba(76, 5, 25, 0.3) 0%, rgba(136, 19, 55, 0.88) 100%)',
            'emerald' => 'linear-gradient(180deg, rgba(2, 44, 34, 0.3) 0%, rgba(6, 78, 59, 0.88) 100%)',
            'amber' => 'linear-gradient(180deg, rgba(69, 26, 3, 0.3) 0%, rgba(120, 53, 15, 0.88) 100%)',
            'subtle' => 'linear-gradient(180deg, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.65) 100%)',
            default => 'linear-gradient(180deg, rgba(15, 23, 42, 0.25) 0%, rgba(15, 23, 42, 0.88) 100%)',
        };
    }
}
