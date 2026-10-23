<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GovernanceApiSendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->canAccessHub('dashboard') || Gate::allows('manage-governance'));
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', 'in:almacen,ventas,logistica,general'],
            'message' => ['required', 'string', 'max:2000'],
            'reference_type' => ['nullable', 'string', 'max:50'],
            'reference_code' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'string', 'in:normal,high,urgent'],
            'target_role' => ['nullable', 'string', 'max:50'],
            'sender_name' => ['nullable', 'string', 'max:120'],
            'sender_role' => ['nullable', 'string', 'max:50'],
        ];
    }
}
