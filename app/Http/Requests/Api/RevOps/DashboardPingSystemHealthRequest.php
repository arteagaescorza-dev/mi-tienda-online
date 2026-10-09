<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DashboardPingSystemHealthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('view-dashboard');
    }

    public function rules(): array
    {
        return [];
    }
}
