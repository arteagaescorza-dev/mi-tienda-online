<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GovernanceApiGetChatMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->canAccessHub('dashboard') || Gate::allows('manage-governance'));
    }

    public function rules(): array
    {
        return [];
    }
}
