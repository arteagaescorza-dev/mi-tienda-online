<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class VerifyThreeWayMatchingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_code' => 'required|string|max:50',
            'supplier_id' => 'required|string|max:50',
            'supplier_name' => 'required|string|max:150',
            'po_qty' => 'required|integer|min:1',
            'po_total_amount' => 'required|numeric|min:0',
            'warehouse_received_qty' => 'required|integer|min:0',
            'warehouse_damaged_qty' => 'nullable|integer|min:0',
            'invoice_number' => 'required|string|max:80',
            'invoice_qty' => 'required|integer|min:1',
            'invoice_gross_amount' => 'required|numeric|min:0',
            'inspected_by' => 'nullable|string|max:100',
        ];
    }
}
