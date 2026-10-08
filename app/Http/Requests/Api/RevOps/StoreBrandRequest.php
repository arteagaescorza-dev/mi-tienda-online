<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Models\Brand;

class StoreBrandRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('manage-catalog');
    }

    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => strip_tags(trim((string) $this->name)),
            ]);
        }
        if ($this->has('description')) {
            $this->merge([
                'description' => strip_tags(trim((string) $this->description)),
            ]);
        }
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:150',
            'supplier_id' => 'required|exists:suppliers,id',
            'slug' => 'nullable|string|max:150|unique:brands,slug',
            'description' => 'nullable|string|max:1000',
            'logo_url' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'supplier_id.required' => 'Toda marca comercial debe estar obligatoriamente asociada a un proveedor (Regla 1).',
            'supplier_id.exists' => 'El proveedor seleccionado no existe en el registro.',
        ];
    }
}

