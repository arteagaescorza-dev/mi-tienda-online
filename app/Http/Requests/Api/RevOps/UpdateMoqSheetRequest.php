<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMoqSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:150',
            'title' => 'sometimes|required|string|max:200',
            'validity' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'tiers' => 'sometimes|required|array|min:1',
            'rows' => 'sometimes|required|array|min:1',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
