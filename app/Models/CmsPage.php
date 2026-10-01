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
        'content_html',
        'show_in_navbar',
        'show_in_mobile',
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
}
