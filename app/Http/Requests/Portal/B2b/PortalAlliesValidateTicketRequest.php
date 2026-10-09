<?php

namespace App\Http\Requests\Portal\B2b;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PortalAlliesValidateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-b2b');
    }

    public function rules(): array
    {
        return [
            'voucher_code' => 'required|string|max:50',
        ];
    }
}
