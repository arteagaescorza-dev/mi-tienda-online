<?php

namespace App\Http\Requests\Portal\Marketing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class LeadImportProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-marketing');
    }

    public function rules(): array
    {
        return [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ];
    }
}
