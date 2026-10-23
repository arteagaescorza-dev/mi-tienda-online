<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GovernanceApiGetAuditLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-governance');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'error' => 'Acceso denegado por politica de gobernanza y auditoria RBAC (TASK-DSH-09).',
                'message' => 'Solo los perfiles con rango de superadministrador pueden auditar la bitacora omnicanal.',
            ], 403)
        );
    }
}
