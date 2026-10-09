<?php

namespace App\Http\Requests\Portal\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class FulfillPackagingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'package_type_id' => 'nullable|exists:package_types,id',
        ];
    }
}
