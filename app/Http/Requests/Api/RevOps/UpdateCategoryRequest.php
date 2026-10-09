<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'slug' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }
}
