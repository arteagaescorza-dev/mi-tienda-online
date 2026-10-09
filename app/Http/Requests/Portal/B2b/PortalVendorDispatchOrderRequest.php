<?php

namespace App\Http\Requests\Portal\B2b;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PortalVendorDispatchOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-b2b');
    }

    public function rules(): array
    {
        return [
            'carrier_name' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'remission_number' => 'nullable|string|max:80',
            'packages_count' => 'nullable|integer|min:1',
            'driver_name' => 'nullable|string|max:100',
            'license_plate' => 'nullable|string|max:30',
            'dispatched_units' => 'nullable|integer|min:1',
            'estimated_arrival_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
