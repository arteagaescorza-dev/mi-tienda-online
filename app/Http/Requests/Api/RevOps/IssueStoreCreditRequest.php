<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class IssueStoreCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'credit_amount' => 'nullable|numeric|min:0',
            'technical_notes' => 'nullable|string|max:500',
        ];
    }
}
