<?php

namespace App\Http\Requests\Api\RevOps;

use Illuminate\Foundation\Http\FormRequest;

class UploadShippingReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gates will be handled in the controller
    }

    public function rules(): array
    {
        return [
            'carrier_id' => 'required|string|in:dhl,dhl_express,skydropx,estafeta,fedex,99minutos,paquetexpress',
            'invoice_folio' => 'required|string|max:100',
            'report_file' => 'nullable|file',
            'invoice_rows' => 'nullable|array',
            'raw_csv' => 'nullable|string',
            'audited_by' => 'nullable|string|max:100',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('carrier') && !$this->has('carrier_id')) {
            $this->merge(['carrier_id' => $this->input('carrier')]);
        }
    }
}
