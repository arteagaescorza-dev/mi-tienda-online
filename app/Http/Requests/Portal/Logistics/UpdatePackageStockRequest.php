<?php

namespace App\Http\Requests\Portal\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdatePackageStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'stock_available' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|integer|min:0',
        ];
    }
}
