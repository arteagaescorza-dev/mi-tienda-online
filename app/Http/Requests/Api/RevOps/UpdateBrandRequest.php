<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateBrandRequest extends FormRequest
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
        $brandId = $this->route('id');

        return [
            'name' => 'nullable|string|max:150',
            'supplier_id' => 'nullable',
            'slug' => 'nullable|string|max:150|unique:brands,slug,' . $brandId,
            'description' => 'nullable|string|max:1000',
            'logo_url' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ];
    }
}

