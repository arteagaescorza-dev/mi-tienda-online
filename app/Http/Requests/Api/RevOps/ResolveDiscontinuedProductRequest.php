<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class ResolveDiscontinuedProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:hide,archive,delete',
            'reason' => 'nullable|string|max:255',
        ];
    }
}
