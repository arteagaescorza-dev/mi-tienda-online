<?php

namespace App\Http\Requests\Portal\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class InventoryMovementStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-inventory');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:increase,decrease'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
