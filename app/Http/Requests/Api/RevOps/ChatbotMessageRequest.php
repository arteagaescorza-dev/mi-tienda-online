<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => 'nullable|integer',
            'customer_name' => 'nullable|string|max:120',
            'message' => 'required|string|max:1000',
            'sender' => 'nullable|string|in:user,bot,agent',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('customer_name')) {
            $this->merge(['customer_name' => strip_tags($this->customer_name)]);
        }
        if ($this->has('message')) {
            $this->merge(['message' => strip_tags($this->message)]);
        }
    }
}

