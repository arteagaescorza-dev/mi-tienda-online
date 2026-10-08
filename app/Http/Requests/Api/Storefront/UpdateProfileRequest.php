<?php

namespace App\Http\Requests\Api\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        if ($this->has('preferred_cut')) {
            $this->merge(['preferred_cut' => strip_tags(trim((string) $this->preferred_cut))]);
        }
    }

    public function rules()
    {
        return [
            'waist_cm' => 'required|integer|min:50|max:160',
            'preferred_cut' => 'nullable|string|max:100',
            'silhouettes' => 'nullable|array',
            'silhouettes.*' => 'string|max:100',
        ];
    }
}

