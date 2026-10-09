<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreMoqSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:150',
            'title' => 'required|string|max:200',
            'validity' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'tiers' => 'required|array|min:1',
            'tiers.*.key' => 'required|string|max:50',
            'tiers.*.label' => 'required|string|max:100',
            'tiers.*.min_qty' => 'required|integer|min:1',
            'tiers.*.color' => 'nullable|string|max:30',
            'tiers.*.badge_class' => 'nullable|string|max:100',
            'rows' => 'required|array|min:1',
            'rows.*.category' => 'required|string|max:150',
            'rows.*.prices' => 'required|array',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
