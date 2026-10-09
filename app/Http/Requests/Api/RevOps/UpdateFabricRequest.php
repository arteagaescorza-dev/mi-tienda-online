<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFabricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:80|unique:apparel_fabrics,name,' . $id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }
}
