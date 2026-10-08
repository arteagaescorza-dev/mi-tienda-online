<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate is handled in the controller
    }

    public function rules(): array
    {
        return [
            'freeShippingThreshold' => 'nullable|numeric|min:0',
            'flatRate' => 'nullable|numeric|min:0',
            'localPickupEnabled' => 'nullable|boolean',
            'localPickupLocation' => 'nullable|string|max:255',
            'localPickupSchedule' => 'nullable|string|max:255',
        ];
    }
}
