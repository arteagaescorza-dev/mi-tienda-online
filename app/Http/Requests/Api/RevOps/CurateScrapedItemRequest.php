<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class CurateScrapedItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject',
            'curated_title' => 'required_if:action,approve|string|max:200',
            'curated_brand' => 'nullable|string|max:80',
            'supplier_id' => 'nullable',
            'curated_category' => 'nullable|string|max:100',
            'curated_subcategory' => 'nullable|string|max:100',
            'curated_fabric' => 'nullable|string|max:80',
            'curated_color' => 'nullable|string|max:80',
            'curated_design_type' => 'nullable|string|in:sin_diseno,con_diseno',
            'curated_website_section' => 'nullable|string|max:120',
            'curated_price' => 'required_if:action,approve|numeric|min:0',
            'curated_cost' => 'nullable|numeric|min:0',
            'curated_stock' => 'nullable|integer|min:0',
            'curated_type' => 'nullable|string|in:propio,dropship',
            'curated_description' => 'nullable|string',
            'bullet_points' => 'nullable|array',
            'seo_tags' => 'nullable|array',
            'curated_images' => 'nullable|array',
            'image_color_map' => 'nullable|array',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ];
    }
}
