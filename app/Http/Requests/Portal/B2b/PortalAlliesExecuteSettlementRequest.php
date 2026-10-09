<?php

namespace App\Http\Requests\Portal\B2b;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PortalAlliesExecuteSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-b2b');
    }

    public function rules(): array
    {
        return [
            'settlement_type' => 'nullable|string|in:spei_transfer,in_kind_apparel,sponsorship_vip',
        ];
    }
}
