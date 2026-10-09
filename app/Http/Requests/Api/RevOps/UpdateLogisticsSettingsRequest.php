<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateLogisticsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'rma_retention_days' => 'required|integer|min:1|max:365',
        ];
    }
}
