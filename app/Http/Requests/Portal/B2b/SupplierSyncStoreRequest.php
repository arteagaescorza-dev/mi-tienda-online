<?php

namespace App\Http\Requests\Portal\B2b;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SupplierSyncStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-b2b');
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array'],
            'products.*.supplier_sku' => ['required', 'string', 'max:255'],
            'products.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'products.*.supplier_url' => ['nullable', 'url', 'max:2048'],
            'products.*.price' => ['nullable', 'numeric', 'min:0'],
            'products.*.availability' => ['required', 'in:unknown,in_stock,out_of_stock'],
            'products.*.raw_snapshot' => ['nullable', 'array'],
        ];
    }
}
