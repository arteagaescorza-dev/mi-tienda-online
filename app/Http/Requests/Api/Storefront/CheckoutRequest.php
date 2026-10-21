<?php

namespace App\Http\Requests\Api\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Sanitization against XSS
        if ($this->has('notes')) {
            $this->merge(['notes' => strip_tags(trim((string) $this->notes))]);
        }
    }

    public function rules()
    {
        return [
            'cart_id' => 'nullable|string',
            'customer' => 'nullable|array',
            'customer.name' => 'nullable|string|max:255',
            'customer.email' => 'nullable|string|max:255',
            'customer.phone' => 'nullable|string|max:50',
            'shipping' => 'nullable|array',
            'shipping.street' => 'nullable|string|max:255',
            'shipping.exterior_number' => 'nullable|string|max:50',
            'shipping.interior_number' => 'nullable|string|max:50',
            'shipping.colony' => 'nullable|string|max:255',
            'shipping.postal_code' => 'nullable|string|max:20',
            'shipping.city' => 'nullable|string|max:255',
            'shipping.state' => 'nullable|string|max:255',
            'shipping.references' => 'nullable|string|max:500',
            'shipping_address' => 'nullable|array',
            'shipping_address.street' => 'nullable|string|max:255',
            'shipping_address.city' => 'nullable|string|max:255',
            'shipping_address.state' => 'nullable|string|max:255',
            'shipping_address.zip' => 'nullable|string|max:20',
            'shipping_address.country' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
            'coupon_code' => 'nullable|string|max:100',
            'dispatch_strategy' => 'nullable|string|in:standard,split',
            'universe' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ];
    }
}

