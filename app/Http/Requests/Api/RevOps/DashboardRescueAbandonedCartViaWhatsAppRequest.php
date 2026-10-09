<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DashboardRescueAbandonedCartViaWhatsAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('view-dashboard');
    }

    public function rules(): array
    {
        return [
            'case_id' => 'nullable|integer',
            'phone' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'cart_token' => 'nullable|string',
            'discount_code' => 'nullable|string',
            'custom_message' => 'nullable|string',
        ];
    }
}
