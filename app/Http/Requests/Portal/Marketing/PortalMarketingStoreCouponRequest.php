<?php

namespace App\Http\Requests\Portal\Marketing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PortalMarketingStoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-marketing');
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:40|unique:coupons,code',
            'discount_type' => 'required|in:percent,fixed_amount,free_shipping,bundle_gift',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'target_universe' => 'required|string|max:30',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ];
    }
}
