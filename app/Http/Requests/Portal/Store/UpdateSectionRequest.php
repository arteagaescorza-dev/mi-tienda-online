<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate is handled in the controller
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_pages,slug',
            'icon' => 'nullable|string|max:50',
            'image_url' => 'nullable|string|max:500',
            'overlay_gradient' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:30',
            'image_source_mode' => 'nullable|string|in:custom,catalog',
            'overlay_color_start' => 'nullable|string|max:30',
            'overlay_color_end' => 'nullable|string|max:30',
            'overlay_opacity_start' => 'nullable|integer|min:0|max:100',
            'overlay_opacity_end' => 'nullable|integer|min:0|max:100',
            'gallery_images_urls' => 'nullable|string',
            'filter_universe' => 'nullable|string|max:40',
            'filter_category' => 'nullable|string|max:80',
            'filter_tags' => 'nullable|string|max:255',
            'filter_supplier' => 'nullable|string|max:100',
            'content_html' => 'required|string',
            'show_in_navbar' => 'nullable|boolean',
            'show_in_mobile' => 'nullable|boolean',
            'navbar_order' => 'nullable|integer',
            'show_in_footer' => 'nullable|boolean',
            'footer_order' => 'nullable|integer',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'status' => 'nullable|in:active,paused,draft,archived',
            'image_file' => 'nullable|image|max:5120',
            'gallery_files.*' => 'nullable|image|max:5120',
        ];
    }
}
