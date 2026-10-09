<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GenerateManifestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'courier' => 'required|string|in:dhl_express,estafeta,fedex,paquetexpress',
            'driver_name' => 'nullable|string|max:100',
            'driver_badge' => 'nullable|string|max:50',
            'shipment_ids' => 'nullable|array',
        ];
    }
}
