<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:40',
            'order_amount' => 'required|numeric|min:0',
            'universe' => 'nullable|string|in:all,terra,marte,venus,tiresias',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strip_tags($this->code)]);
        }
    }
}

