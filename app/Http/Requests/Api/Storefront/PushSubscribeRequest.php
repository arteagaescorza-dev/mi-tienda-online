<?php

namespace App\Http\Requests\Api\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class PushSubscribeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'endpoint' => 'required|string',
            'keys' => 'nullable|array',
            'p256dh_key' => 'nullable|string',
            'auth_token' => 'nullable|string',
            'topics' => 'nullable|array',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize string inputs to prevent XSS
        if ($this->has('endpoint')) {
            $this->merge([
                'endpoint' => strip_tags($this->endpoint)
            ]);
        }
        if ($this->has('p256dh_key')) {
            $this->merge([
                'p256dh_key' => strip_tags($this->p256dh_key)
            ]);
        }
        if ($this->has('auth_token')) {
            $this->merge([
                'auth_token' => strip_tags($this->auth_token)
            ]);
        }
    }
}

