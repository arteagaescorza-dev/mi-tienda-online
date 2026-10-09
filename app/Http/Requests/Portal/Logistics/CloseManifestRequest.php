<?php

namespace App\Http\Requests\Portal\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CloseManifestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'courier' => 'required|string',
            'driver_name' => 'required|string|max:100',
            'driver_badge_or_truck' => 'required|string|max:50',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'integer|exists:shipments,id',
        ];
    }
}
