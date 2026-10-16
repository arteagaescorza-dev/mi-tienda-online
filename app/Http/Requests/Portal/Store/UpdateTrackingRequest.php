<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-cms');
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
