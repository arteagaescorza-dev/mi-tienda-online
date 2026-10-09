<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20',
            'label' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            'waist_min_cm' => 'nullable|numeric|min:0',
            'waist_max_cm' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
