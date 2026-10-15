<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:500',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'universe' => 'nullable|string|max:32',
            'type' => 'nullable|string|max:60',
            'badge' => 'nullable|string|max:80',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|string|in:active,draft,archived',
            'is_active' => 'nullable|boolean',
            'query_rules' => 'nullable|array',
            'is_new' => 'nullable|boolean',
            'has_discount' => 'nullable|boolean',
            'fabric_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'subcategory_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la sección de tienda es obligatorio.',
        ];
    }
}
