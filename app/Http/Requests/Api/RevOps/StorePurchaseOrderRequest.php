<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_number' => 'nullable|string|max:50',
            'supplier_id' => 'nullable|integer',
            'supplier' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
            'delivery_commitment_date' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'priority' => 'nullable|string|max:30',
            'has_credit_deduction' => 'nullable|boolean',
            'credit_agreement_id' => 'nullable|integer',
            'credit_note_ref' => 'nullable|string|max:50',
            'credit_note_amount' => 'nullable|numeric|min:0',
            'credit_note_reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.product_name' => 'nullable|string|max:200',
            'items.*.qty' => 'nullable|integer|min:0',
            'items.*.cost_unit' => 'nullable|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.sizes' => 'nullable|array',
            'items.*.priority' => 'nullable|string|max:30',
            'moq_sheet_id' => 'nullable|integer',
            'moq_tier_label' => 'nullable|string|max:100',
            'po_snapshot' => 'nullable|array',
        ];
    }
}
