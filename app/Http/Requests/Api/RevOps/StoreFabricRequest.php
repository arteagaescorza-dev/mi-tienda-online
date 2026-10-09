<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreFabricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:80|unique:apparel_fabrics,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'El nombre del tipo de tela es obligatorio.',
            'name.unique' => 'Ya existe un tipo de tela registrado con este nombre.',
        ];
    }
}
