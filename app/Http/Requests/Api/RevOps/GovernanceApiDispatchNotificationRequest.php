<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GovernanceApiDispatchNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-governance');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'in:critical,urgent,warning,info,success'],
            'category' => ['nullable', 'string', 'max:50'],
            'target_role' => ['nullable', 'string', 'max:50'],
            'reference_code' => ['nullable', 'string', 'max:50'],
            'deep_link' => ['nullable', 'string', 'max:255'],
            'action_label' => ['nullable', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
