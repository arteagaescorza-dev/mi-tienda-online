<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AlliesApiExecuteSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-b2b');
    }

    public function rules(): array
    {
        return [
            'settlement_type' => 'required|in:spei_transfer,in_kind_apparel,sponsorship_vip',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
