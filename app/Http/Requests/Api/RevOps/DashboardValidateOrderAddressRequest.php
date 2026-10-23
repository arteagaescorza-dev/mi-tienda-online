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

    protected function prepareForValidation(): void
    {
        if ($this->has('ext_number') && !$this->has('exterior_number')) {
            $this->merge(['exterior_number' => $this->input('ext_number')]);
        }
        if ($this->has('int_number') && !$this->has('interior_number')) {
            $this->merge(['interior_number' => $this->input('int_number')]);
        }
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
