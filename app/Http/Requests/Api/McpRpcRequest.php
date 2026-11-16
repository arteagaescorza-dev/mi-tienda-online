<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class McpRpcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jsonrpc' => ['required', 'string', 'in:2.0'],
            'id' => ['nullable'],
            'method' => ['required', 'string', 'max:120'],
            'params' => ['nullable', 'array'],
        ];
    }
}
