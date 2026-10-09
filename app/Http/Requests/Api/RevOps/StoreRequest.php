<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'tradeName' => 'nullable|string|max:255',
            'legalName' => 'nullable|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'vendorCode' => 'nullable|string|max:50',
            'taxId' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'fiscalRegime' => 'nullable|string|max:150',
            'fiscal_regime' => 'nullable|string|max:150',
            'fiscalAddress' => 'nullable|string|max:500',
            'fiscal_address' => 'nullable|string|max:500',
            'fiscalZip' => 'nullable|string|max:20',
            'fiscal_zip' => 'nullable|string|max:20',
            'invoiceEmail' => 'nullable|email|max:150',
            'invoice_email' => 'nullable|email|max:150',
            'website' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'contactName' => 'required|string|max:150',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'leadTimeDays' => 'nullable|integer|min:1',
            'paymentTerms' => 'nullable|string|max:50',
            'extractionMethod' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:connected,paused,maintenance,archived',
            'origin' => 'nullable|string|in:manual,scraping',
            'brands' => 'nullable|array',
            'brands.*' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }
}
