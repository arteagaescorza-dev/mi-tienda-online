<?php

namespace App\Http\Requests\Portal\Marketing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class LeadStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-marketing');
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:new,contacted,qualified,proposal,won,lost',
        ];
    }
}
