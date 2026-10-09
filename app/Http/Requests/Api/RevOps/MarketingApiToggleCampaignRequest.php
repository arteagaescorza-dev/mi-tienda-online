<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class MarketingApiToggleCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-marketing');
    }

    public function rules(): array
    {
        return [];
    }
}
