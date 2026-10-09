<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\Ally;
use App\Models\AllyLeadCapture;
use App\Services\ClearingHouseSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RevOps\AlliesApiGetClearingBalanceRequest;
use App\Http\Requests\Api\RevOps\AlliesApiExecuteSettlementRequest;
use App\Http\Requests\Api\RevOps\AlliesApiValidateVoucherRequest;
use App\Http\Requests\Api\RevOps\AlliesApiExportDoorListCsvRequest;
use Illuminate\Support\Facades\Gate;

use Symfony\Component\HttpFoundation\StreamedResponse;

class AlliesApiController extends Controller
{
    protected ClearingHouseSettlementService $settlementService;

    public function __construct(ClearingHouseSettlementService $settlementService)
    {
        $this->settlementService = $settlementService;
    }

    /**
     * Devuelve el cálculo en vivo del balance neto de la cámara de compensación (TASK-ALY-04).
     */
    public function getClearingBalance(AlliesApiGetClearingBalanceRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        
        $ally = Ally::findOrFail($id);
        
        // Regla de Negocio: Mínimo $500 MXN para corte de transferencia SPEI
        if ($validated['settlement_type'] === 'spei_transfer' && $ally->clearing_balance < 500) {
            return response()->json([
                'success' => false,
                'error' => 'El monto acumulado (
        $calc = $this->settlementService->calculateNetBalance($ally);

        return response()->json($calc);
    }

    /**
     * Ejecuta la liquidación del balance contable e inyecta la cuenta por pagar en Finanzas (TASK-INT-19).
     */
    public function executeSettlement(AlliesApiExecuteSettlementRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = 

        $ally = Ally::findOrFail($id);
        $settlement = $this->settlementService->executeSettlement(
            $ally,
            $validated['settlement_type'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'settlement_code' => $settlement->code,
            'settlement_type' => $settlement->settlement_type,
            'net_balance' => (float) $settlement->net_balance,
            'status' => $settlement->status,
            'accounting_reference' => $settlement->accounting_reference,
            'message' => 'Liquidación de cámara ejecutada con asiento contable',
        ]);
    }

    /**
     * Validador de taquilla / puerta anti-duplicados (TASK-ALY-05).
     */
    public function validateVoucher(AlliesApiValidateVoucherRequest $request): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = 

        $code = strtoupper(trim($validated['voucher_code']));
        $lead = AllyLeadCapture::where('voucher_code', $code)->first();

        if (!$lead) {
            return response()->json([
                'valid' => false,
                'status' => 'not_found',
                'message' => "El voucher {$code} no existe en la base de datos central.",
            ], 404);
        }

        if ($lead->is_redeemed) {
            return response()->json([
                'valid' => false,
                'status' => 'already_redeemed',
                'message' => "ALERTA ANTI-DUPLICADO: El voucher {$code} ya fue canjeado a las {$lead->redeemed_at} por {$lead->redeemed_by}.",
                'lead_name' => $lead->lead_name,
                'masked_phone' => preg_replace('/(\+?52\s?55\s?)\d{4}(\s?\d{4})/', '$1 **** $2', $lead->lead_whatsapp),
            ], 422);
        }

        $lead->is_redeemed = true;
        $lead->redeemed_at = now();
        $lead->redeemed_by = $validated['redeemed_by'] ?? 'Staff Puerta Taquilla #1';
        $lead->save();

        $maskedPhone = preg_replace('/(\+?52\s?55\s?)\d{4}(\s?\d{4})/', '$1 **** $2', $lead->lead_whatsapp);

        return response()->json([
            'valid' => true,
            'status' => 'success',
            'message' => "Voucher {$code} validado con éxito para {$lead->lead_name} ({$maskedPhone}). Acceso autorizado.",
            'lead_name' => $lead->lead_name,
            'masked_phone' => $maskedPhone,
        ]);
    }

    /**
     * Exporta la lista de asistentes en CSV con BOM UTF-8 y números de teléfono enmascarados (Data Masking).
     */
    public function exportDoorListCsv(AlliesApiExportDoorListCsvRequest $request, int $id): StreamedResponse
    {
        Gate::authorize('manage-b2b');

        $ally = Ally::findOrFail($id);
        $leads = AllyLeadCapture::where('ally_id', $ally->id)->get();

        $filename = "lista_taquilla_{$ally->code}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($leads, $ally) {
            // BOM UTF-8 para compatibilidad con Excel
            echo "\xEF\xBB\xBF";
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Folio Voucher', 'Nombre Asistente', 'Telefono (Data Masking)', 'Universo', 'Estado Canje', 'Fecha Registro']);

            foreach ($leads as $lead) {
                $masked = preg_replace('/(\+?52\s?55\s?)\d{4}(\s?\d{4})/', '$1 **** $2', $lead->lead_whatsapp);
                fputcsv($handle, [
                    $lead->voucher_code,
                    $lead->lead_name,
                    $masked,
                    strtoupper($lead->universe),
                    $lead->is_redeemed ? 'Canjeado' : 'Pendiente',
                    $lead->created_at ? $lead->created_at->format('Y-m-d H:i') : date('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
 . $ally->clearing_balance . ') no alcanza el mínimo requerido ($500.00 MXN) para procesar un pago vía SPEI. El saldo se acumulará al siguiente corte.'
            ], 422);
        }

        $calc = $this->settlementService->calculateNetBalance($ally);

        return response()->json($calc);
    }

    /**
     * Ejecuta la liquidación del balance contable e inyecta la cuenta por pagar en Finanzas (TASK-INT-19).
     */
    public function executeSettlement(AlliesApiExecuteSettlementRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = 

        $ally = Ally::findOrFail($id);
        $settlement = $this->settlementService->executeSettlement(
            $ally,
            $validated['settlement_type'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'settlement_code' => $settlement->code,
            'settlement_type' => $settlement->settlement_type,
            'net_balance' => (float) $settlement->net_balance,
            'status' => $settlement->status,
            'accounting_reference' => $settlement->accounting_reference,
            'message' => 'Liquidación de cámara ejecutada con asiento contable',
        ]);
    }

    /**
     * Validador de taquilla / puerta anti-duplicados (TASK-ALY-05).
     */
    public function validateVoucher(AlliesApiValidateVoucherRequest $request): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = 

        $code = strtoupper(trim($validated['voucher_code']));
        $lead = AllyLeadCapture::where('voucher_code', $code)->first();

        if (!$lead) {
            return response()->json([
                'valid' => false,
                'status' => 'not_found',
                'message' => "El voucher {$code} no existe en la base de datos central.",
            ], 404);
        }

        if ($lead->is_redeemed) {
            return response()->json([
                'valid' => false,
                'status' => 'already_redeemed',
                'message' => "ALERTA ANTI-DUPLICADO: El voucher {$code} ya fue canjeado a las {$lead->redeemed_at} por {$lead->redeemed_by}.",
                'lead_name' => $lead->lead_name,
                'masked_phone' => preg_replace('/(\+?52\s?55\s?)\d{4}(\s?\d{4})/', '$1 **** $2', $lead->lead_whatsapp),
            ], 422);
        }

        $lead->is_redeemed = true;
        $lead->redeemed_at = now();
        $lead->redeemed_by = $validated['redeemed_by'] ?? 'Staff Puerta Taquilla #1';
        $lead->save();

        $maskedPhone = preg_replace('/(\+?52\s?55\s?)\d{4}(\s?\d{4})/', '$1 **** $2', $lead->lead_whatsapp);

        return response()->json([
            'valid' => true,
            'status' => 'success',
            'message' => "Voucher {$code} validado con éxito para {$lead->lead_name} ({$maskedPhone}). Acceso autorizado.",
            'lead_name' => $lead->lead_name,
            'masked_phone' => $maskedPhone,
        ]);
    }

    /**
     * Exporta la lista de asistentes en CSV con BOM UTF-8 y números de teléfono enmascarados (Data Masking).
     */
    public function exportDoorListCsv(AlliesApiExportDoorListCsvRequest $request, int $id): StreamedResponse
    {
        Gate::authorize('manage-b2b');

        $ally = Ally::findOrFail($id);
        $leads = AllyLeadCapture::where('ally_id', $ally->id)->get();

        $filename = "lista_taquilla_{$ally->code}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($leads, $ally) {
            // BOM UTF-8 para compatibilidad con Excel
            echo "\xEF\xBB\xBF";
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Folio Voucher', 'Nombre Asistente', 'Telefono (Data Masking)', 'Universo', 'Estado Canje', 'Fecha Registro']);

            foreach ($leads as $lead) {
                $masked = preg_replace('/(\+?52\s?55\s?)\d{4}(\s?\d{4})/', '$1 **** $2', $lead->lead_whatsapp);
                fputcsv($handle, [
                    $lead->voucher_code,
                    $lead->lead_name,
                    $masked,
                    strtoupper($lead->universe),
                    $lead->is_redeemed ? 'Canjeado' : 'Pendiente',
                    $lead->created_at ? $lead->created_at->format('Y-m-d H:i') : date('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
