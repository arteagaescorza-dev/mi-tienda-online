<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ResolveRmaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'resolution_type' => 'required|in:replacement_order,store_credit,rejected_sanitary_violation',
            'has_hygiene_seal_intact' => 'nullable|boolean',
            'has_factory_tag' => 'nullable|boolean',
            'inspector_name' => 'nullable|string|max:100',
            'technical_notes' => 'nullable|string|max:500',
            'replacement_sku' => 'nullable|string|max:60',
            'credit_amount' => 'nullable|numeric|min:0',
        ];
    }
}
