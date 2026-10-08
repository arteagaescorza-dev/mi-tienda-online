<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate is handled in the controller
    }

    public function rules(): array
    {
        return [
            'gtm_id' => 'nullable|string|max:50',
            'ga4_measurement_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
            'facebook_capi_token' => 'nullable|string',
            'facebook_test_event_code' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ];
    }
}
