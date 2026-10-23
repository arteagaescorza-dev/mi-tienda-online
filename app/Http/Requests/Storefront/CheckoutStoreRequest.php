<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any customer can checkout
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'exterior_number' => ['required', 'string', 'max:30'],
            'interior_number' => ['nullable', 'string', 'max:30'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'digits:5'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'delivery_references' => ['nullable', 'string', 'max:1000'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'delivery_method' => ['required', 'string', 'in:paqueteria,tienda,personal'],
            'universe' => ['nullable', 'string', 'in:marte,venus,tiresias,terra'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}

