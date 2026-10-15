<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ReorderSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-cms');
    }

    public function rules(): array
    {
        return [
            'order' => 'required|array',
            'order.*' => 'required|integer',
        ];
    }
}
