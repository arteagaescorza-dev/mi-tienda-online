<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DashboardValidateOrderAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('view-dashboard');
    }

    public function rules(): array
    {
        return [
            'case_id' => 'nullable|integer',
            'order_number' => 'nullable|string',
            'postal_code' => 'required|string|size:5',
            'state' => 'required|string',
            'city' => 'required|string',
            'colonia' => 'required|string',
            'street' => 'required|string',
            'exterior_number' => 'required|string',
            'interior_number' => 'nullable|string',
            'references' => 'nullable|string',
        ];
    }
}
