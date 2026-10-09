<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class ReserveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_id' => 'required|string|max:100',
            'variant_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:5|max:60',
        ];
    }
}
