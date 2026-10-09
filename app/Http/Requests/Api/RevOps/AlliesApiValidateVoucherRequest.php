<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AlliesApiValidateVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-b2b');
    }

    public function rules(): array
    {
        return [
            'voucher_code' => 'required|string|max:50',
            'redeemed_by' => 'nullable|string|max:100',
        ];
    }
}
