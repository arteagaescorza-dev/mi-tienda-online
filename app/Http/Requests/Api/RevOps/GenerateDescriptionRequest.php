<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class GenerateDescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'fabric_type' => 'nullable|string|max:100',
            'color_name' => 'nullable|string|max:100',
            'design_type' => 'nullable|string|max:50',
            'target_audience' => 'nullable|string|max:50',
            'pvp' => 'nullable|numeric|min:0',
        ];
    }
}
