<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:80|unique:apparel_colors,name',
            'hex_code' => 'nullable|string|max:250',
            'hex' => 'nullable|string|max:250',
            'color_type' => 'nullable|string|max:30',
            'type' => 'nullable|string|max:30',
            'gradient_css' => 'nullable|string|max:250',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del color es obligatorio.',
            'name.unique' => 'Este color ya se encuentra registrado.',
        ];
    }
}
