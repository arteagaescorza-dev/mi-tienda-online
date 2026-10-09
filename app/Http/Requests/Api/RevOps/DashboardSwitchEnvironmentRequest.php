<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DashboardSwitchEnvironmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('view-dashboard');
    }

    public function rules(): array
    {
        return [
            'module' => 'required|string|max:50',
            'target_environment' => 'required|in:sandbox,production',
        ];
    }
}
