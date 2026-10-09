<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSizeCurveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'category_scope' => 'nullable|string|max:50',
            'supplier_id' => 'nullable',
            'brand_id' => 'nullable',
            'description' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }
}
