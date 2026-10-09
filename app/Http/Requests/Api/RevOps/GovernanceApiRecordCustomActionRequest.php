<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GovernanceApiRecordCustomActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-governance');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'max:50'],
            'module' => ['required', 'string', 'max:50'],
            'reference_code' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'old_values' => ['nullable', 'array'],
            'new_values' => ['nullable', 'array'],
        ];
    }
}
