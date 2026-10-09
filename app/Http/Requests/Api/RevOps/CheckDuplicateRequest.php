<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class CheckDuplicateRequest extends FormRequest
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
            'category_id' => 'nullable|integer',
            'category' => 'nullable',
            'price' => 'nullable|numeric|min:0',
            'pvp' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'exclude_product_id' => 'nullable|integer',
            'threshold_warning' => 'nullable|integer|min:1|max:100',
            'threshold_match' => 'nullable|integer|min:1|max:100',
        ];
    }
}
