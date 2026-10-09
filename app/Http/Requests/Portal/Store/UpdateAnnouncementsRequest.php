<?php

namespace App\Http\Requests\Portal\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateAnnouncementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-cms');
    }

    public function rules(): array
    {
        return [];
    }
}
