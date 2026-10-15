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
            'brand_name' => 'nullable|string|max:150',
            'brandName' => 'nullable|string|max:150',
            'kicker' => 'nullable|string|max:250',
            'logo_url' => 'nullable|string|max:500',
            'logo_mobile_url' => 'nullable|string|max:500',
            'favicon_url' => 'nullable|string|max:500',
            'logo_file' => 'nullable|file|image|max:5120',
            'logo_mobile_file' => 'nullable|file|image|max:5120',
            'favicon_file' => 'nullable|file|max:2048',
            'primary_color' => 'nullable|string|max:30',
            'secondary_color' => 'nullable|string|max:30',
            'accent_color' => 'nullable|string|max:30',
            'background_color' => 'nullable|string|max:30',
            'surface_color' => 'nullable|string|max:30',
            'text_color' => 'nullable|string|max:30',
            'muted_text_color' => 'nullable|string|max:30',
            'border_color' => 'nullable|string|max:30',
            'typography_heading' => 'nullable|string|max:100',
            'typography_subtitle' => 'nullable|string|max:100',
            'typography_body' => 'nullable|string|max:100',
            'typography_accent' => 'nullable|string|max:100',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
        ];
    }
}
