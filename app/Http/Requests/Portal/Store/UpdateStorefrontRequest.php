<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate is handled in the controller
    }

    public function rules(): array
    {
        return [
            'universe' => 'required|string|in:all,marte,venus,tiresias,terra',
            'name' => 'required|string|max:120',
            'type' => 'nullable|string|in:hero_carousel,brand_pillars,categories_grid,family_showcase,subcategories_slider,tags_cloud,custom_html_css_js,curated_products,editorial_banner,promo_modal',
            'status' => 'required|string|in:active,draft,archived',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'badge' => 'nullable|string|max:80',
            'query_rules' => 'nullable|array',
            'custom_html' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'settings' => 'nullable|array',
            'background_image_file' => 'nullable|image|max:5120',
        ];
    }
}
