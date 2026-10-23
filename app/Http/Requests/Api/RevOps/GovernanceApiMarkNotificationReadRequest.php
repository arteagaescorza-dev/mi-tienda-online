<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GovernanceApiMarkNotificationReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user ? ($user->canAccessHub('dashboard') || Gate::allows('manage-governance')) : true;
    }

    public function rules(): array
    {
        return [];
    }
}
