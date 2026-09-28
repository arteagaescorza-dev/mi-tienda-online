<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsBlogPost extends Model
{
    use HasFactory;

    protected $table = 'cms_blog_posts';

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'body',
        'featured_image',
        'category_name',
        'tags',
        'author_name',
        'reading_time_minutes',
        'seo_meta',
        'status',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'seo_meta' => 'array',
        'reading_time_minutes' => 'integer',
        'published_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at');
    }
}
