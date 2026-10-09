<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|string|max:100',
            'type' => 'required|string|in:add,sub,increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'variant_id' => 'nullable|integer',
            'size' => 'nullable|string|max:20',
        ];
    }
}
