<?php

namespace App\Http\Requests\Portal\B2b;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreAllyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-b2b');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:allies,code',
            'category' => 'required|string|max:50',
            'category_label' => 'nullable|string|max:100',
            'universe' => 'nullable|string|in:marte,venus,tiresias,terra',
            'contact_name' => 'nullable|string|max:120',
            'contact_whatsapp' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:150',
            'social_handle' => 'nullable|string|max:80',
            'status' => 'nullable|string|in:active,inactive,archived,paused,terminated',
            'bank_name' => 'nullable|string|max:60',
            'bank_clabe' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'allowed_discount_types' => 'nullable|array',
            'allowed_discount_types.*' => 'string|in:cover,cover_2x1,free_drink,store_discount,vip_access',
            'max_discount_pct' => 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|string|in:percentage,fixed_per_order',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'lead_acquisition_cost' => 'nullable|numeric|min:0',
        ];
    }
}

