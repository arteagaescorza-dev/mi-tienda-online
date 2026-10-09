<?php

namespace App\Http\Requests\Portal\Logistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateShipmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-logistics');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:label_created,packed,in_transit,out_for_delivery,delivered,failed_attempt,returned',
        ];
    }
}
