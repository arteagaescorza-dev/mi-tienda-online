<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class AuditBomDeficitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bundle_id' => 'nullable',
            'bundle_sku' => 'nullable|string',
            'target_units' => 'nullable|integer|min:1',
            'target_qty' => 'nullable|integer|min:1',
            'batch_reference' => 'nullable|string|max:100',
        ];
    }
}
