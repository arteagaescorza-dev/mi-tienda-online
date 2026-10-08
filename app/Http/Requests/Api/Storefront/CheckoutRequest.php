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
            'shipping_address' => 'required|array',
            'shipping_address.street' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.state' => 'required|string|max:255',
            'shipping_address.zip' => 'required|string|max:10',
            'shipping_address.country' => 'required|string|max:255',
            'payment_method' => 'required|string|in:card,paypal,oxxo,spei',
            'notes' => 'nullable|string|max:500',
        ];
    }
}

