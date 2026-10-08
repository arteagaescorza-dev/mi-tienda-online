<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class FileShippingDiscrepancyClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer',
            'claim_notes' => 'nullable|string|max:1000',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('notes') && !$this->has('claim_notes')) {
            $this->merge(['claim_notes' => $this->input('notes')]);
        }
    }
}
