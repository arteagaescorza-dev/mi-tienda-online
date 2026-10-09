<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:propio,physical,dropship,both',
            'pvp' => 'required|numeric|min:0',
            'regular_price' => 'nullable|numeric|min:0',
            'discount_pct' => 'nullable|numeric|min:0|max:100',
            'cost' => 'required|numeric|min:0',
            'weighted_average_cost' => 'nullable|numeric|min:0',
            'sizes' => 'nullable|array',
            'shades' => 'nullable|array',
            'volumes' => 'nullable|array',
            'location' => 'nullable|string|max:100',
            'fabric_type' => 'nullable|string|max:100',
            'fabric' => 'nullable|string|max:120',
            'fabric_id' => 'nullable|integer',
            'color_name' => 'nullable|string|max:100',
            'color_hex' => 'nullable|string|max:20',
            'color_type' => 'nullable|string|max:50',
            'design_type' => 'nullable|string|max:50',
            'website_section' => 'nullable|string|max:120',
            'section_name' => 'nullable|string|max:120',
            'is_published' => 'nullable|boolean',
            'description' => 'nullable|string',
            'requires_sanitary_control' => 'nullable|boolean',
            'batch_number' => 'nullable|string|max:100',
            'expiration_date' => 'nullable|string|max:50',
            'pao_months' => 'nullable|integer|min:0',
            'supplier_id' => 'nullable',
            'provider' => 'nullable|string|max:100',
            'provider_url' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images_by_color' => 'nullable|array',
            'image_color_map' => 'nullable|array',
        ];
    }
}
