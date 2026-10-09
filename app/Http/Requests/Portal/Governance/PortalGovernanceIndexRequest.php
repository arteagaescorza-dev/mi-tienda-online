<?php

namespace App\Http\Requests\Portal\Governance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PortalGovernanceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-governance');
    }

    public function rules(): array
    {
        return [];
    }
}
