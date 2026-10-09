<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class IssueReplacementOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'replacement_sku' => 'nullable|string|max:60',
            'replacement_variant_id' => 'nullable|integer',
            'technical_notes' => 'nullable|string|max:500',
        ];
    }
}
