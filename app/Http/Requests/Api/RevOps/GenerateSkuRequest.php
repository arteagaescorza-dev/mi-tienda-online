<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'nullable',
            'brand' => 'nullable|string|max:100',
            'supplier_id' => 'nullable',
            'supplier' => 'nullable|string|max:100',
            'fabric' => 'nullable|string|max:100',
            'fabric_type' => 'nullable|string|max:100',
            'color_name' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'variant_code' => 'nullable',
            'variant' => 'nullable',
            'prefix_override' => 'nullable|string|max:10',
        ];
    }
}
