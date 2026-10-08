<?php

namespace App\Http\Requests\Api\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        if ($this->has('tier_name')) {
            $this->merge(['tier_name' => strip_tags(trim((string) $this->tier_name))]);
        }
        if ($this->has('universe')) {
            $this->merge(['universe' => strip_tags(trim((string) $this->universe))]);
        }
    }

    public function rules()
    {
        return [
            'tier_name' => 'nullable|string|max:100',
            'monthly_amount' => 'nullable|numeric|min:0',
            'universe' => 'nullable|string|max:100',
            'card_last4' => 'nullable|string|max:4',
            'card_brand' => 'nullable|string|max:50',
        ];
    }
}

