<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class IssueDebitNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'debit_note_number' => 'required|string|max:80',
            'debit_amount' => 'nullable|numeric|min:0',
        ];
    }
}
