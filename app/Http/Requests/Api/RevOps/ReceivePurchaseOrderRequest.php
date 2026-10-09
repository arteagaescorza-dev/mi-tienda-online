<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
            'batch_number' => 'nullable|string|max:80',
            'expiration_date' => 'nullable|string|max:30',
            'pao_months' => 'nullable|integer|min:0|max:120',
            'sanitary_data' => 'nullable|array',
            'moq_tier_label' => 'nullable|string|max:100',
        ];
    }
}
