<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('color');

        return [
            'name' => 'sometimes|required|string|max:80|unique:apparel_colors,name,' . $id,
            'hex_code' => 'nullable|string|max:250',
            'hex' => 'nullable|string|max:250',
            'color_type' => 'nullable|string|max:30',
            'type' => 'nullable|string|max:30',
            'gradient_css' => 'nullable|string|max:250',
            'is_active' => 'nullable|boolean',
        ];
    }
}
