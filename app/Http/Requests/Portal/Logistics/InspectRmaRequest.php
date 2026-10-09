<?php

namespace App\Http\Requests\Portal\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class InspectRmaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'inspector_name' => 'required|string|max:100',
            'has_hygiene_seal_intact' => 'nullable',
            'has_factory_tag' => 'nullable',
            'verdict' => 'required|in:approved,rejected_sanitary_violation',
            'technical_notes' => 'nullable|string|max:500',
            'resolution_type' => 'nullable|string|in:replacement_shipped,store_credit_wallet,replacement_order,store_credit,refund',
            'replacement_sku' => 'nullable|string|max:60',
            'credit_amount' => 'nullable|numeric|min:0',
        ];
    }
}
