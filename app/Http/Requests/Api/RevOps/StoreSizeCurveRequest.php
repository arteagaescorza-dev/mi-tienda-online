<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreSizeCurveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'category_scope' => 'nullable|string|max:50',
            'supplier_id' => 'nullable',
            'brand_id' => 'nullable',
            'description' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sizes' => 'nullable|array',
            'sizes.*.code' => 'required|string|max:20',
            'sizes.*.label' => 'nullable|string|max:100',
            'sizes.*.sort_order' => 'nullable|integer',
            'sizes.*.waist_min_cm' => 'nullable|numeric|min:0',
            'sizes.*.waist_max_cm' => 'nullable|numeric|min:0',
            'sizes.*.is_active' => 'nullable|boolean',
        ];
    }
}
