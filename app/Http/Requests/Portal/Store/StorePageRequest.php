<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate is handled in the controller
    }

    public function rules(): array
    {
        return [
            'order' => 'required|array',
            'order.*' => 'required|integer',
        ];
    }
}
