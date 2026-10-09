<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class RecordCycleCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'counts' => 'required|array|min:1',
            'counts.*.variant_id' => 'nullable|integer',
            'counts.*.sku' => 'nullable|string|max:100',
            'counts.*.physical_counted' => 'required|integer|min:0',
            'counts.*.reason' => 'nullable|string|max:255',
            'reconcile_kardex' => 'nullable|boolean',
        ];
    }
}
