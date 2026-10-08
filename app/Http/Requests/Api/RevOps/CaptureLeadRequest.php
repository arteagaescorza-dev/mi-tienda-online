<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class CaptureLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'universe' => 'nullable|string|in:terra,marte,venus,tiresias',
            'campaign_source' => 'nullable|string|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => strip_tags($this->name)]);
        }
        if ($this->has('campaign_source')) {
            $this->merge(['campaign_source' => strip_tags($this->campaign_source)]);
        }
    }
}

