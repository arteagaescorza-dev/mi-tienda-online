<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'purchase_order_id' => 'nullable|integer|exists:purchase_orders,id',
            'reference_folio' => 'nullable|string|max:50',
            'agreement_type' => 'nullable|string|max:40',
            'discrepancy_type' => 'nullable|string|max:50',
            'missing_units_count' => 'nullable|integer|min:0',
            'damaged_units_count' => 'nullable|integer|min:0',
            'unit_cost_applied' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            'authorized_by_vendor_contact' => 'nullable|string|max:150',
            'discrepancy_minutes' => 'nullable|string|max:2000',
            'resolution_notes' => 'nullable|string|max:1000',
        ];
    }
}
