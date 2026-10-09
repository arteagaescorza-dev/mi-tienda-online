<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable',
            'bundle_id' => 'nullable',
            'sku' => 'required|string|max:150',
            'name' => 'required|string|max:150',
            'type' => 'nullable|string|max:40',
            'bundle_type' => 'nullable|string|max:40',
            'subscription_price' => 'nullable|numeric|min:0',
            'subscriptionPrice' => 'nullable|numeric|min:0',
            'bundle_price' => 'nullable|numeric|min:0',
            'regular_price' => 'nullable|numeric|min:0',
            'regularPrice' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'isActive' => 'nullable|boolean',
            'components' => 'required|array|min:1',
            'components.*.sku' => 'required|string|max:100',
            'components.*.size' => 'nullable|string|max:20',
            'components.*.qty' => 'required|integer|min:1',
        ];
    }
}
