<?php

namespace App\Http\Requests\Portal\Governance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PortalGovernanceUpdateEnvironmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-governance');
    }

    public function rules(): array
    {
        return [
            'module' => ['required', 'string', 'max:50'],
            'target_environment' => ['required', 'string', 'in:sandbox,production'],
            'lock_mock_payments' => ['nullable', 'boolean'],
        ];
    }
}
