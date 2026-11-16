<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InternalCronRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'command' => [
                'required',
                'string',
                'in:supplier:scrape,supplier:prune-runs,stock:cleanup-expired-locks,telemetry:check-apis,inventory:evaluate-rop,finance:process-dunning',
            ],
        ];
    }
}
