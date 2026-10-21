<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RevOps\AlliesApiGetClearingBalanceRequest;
use App\Http\Requests\Api\RevOps\AlliesApiExecuteSettlementRequest;
use App\Http\Requests\Api\RevOps\AlliesApiValidateVoucherRequest;
use App\Http\Requests\Api\RevOps\AlliesApiExportDoorListCsvRequest;
use App\Models\Ally;
use App\Models\AllyType;
use App\Models\AllyDiscountType;
use App\Models\AllyLeadCapture;
use App\Services\ClearingHouseSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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
        $calc = $this->settlementService->calculateNetBalance($ally);

        return response()->json($calc);
    }

    /**
     * Ejecuta la liquidación del balance contable e inyecta la cuenta por pagar en Finanzas (TASK-INT-19).
     */
    public function executeSettlement(AlliesApiExecuteSettlementRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = $request->validated();
        $ally = Ally::findOrFail($id);

        // Regla de Negocio: Mínimo $500 MXN para corte de transferencia SPEI
        if ($validated['settlement_type'] === 'spei_transfer' && $ally->clearing_balance < 500) {
            return response()->json([
                'success' => false,
                'error' => 'El monto acumulado (' . $ally->clearing_balance . ') no alcanza el mínimo requerido ($500.00 MXN) para procesar un pago vía SPEI. El saldo se acumulará al siguiente corte.'
            ], 422);
        }

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

        $validated = $request->validated();
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

    /**
     * Alterna el estado de archivo de un aliado comercial (API).
     */
    public function archiveAlly(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $ally = Ally::findOrFail($id);
        $isArchiving = ($ally->status === 'active');
        $newStatus = $isArchiving ? 'archived' : 'active';
        $ally->update(['status' => $newStatus]);

        if ($isArchiving) {
            $ally->campaignsDirA()->where('status', 'active')->update(['status' => 'paused']);
            $ally->couponsDirB()->where('status', 'active')->update(['status' => 'paused']);
            $msg = "Aliado '{$ally->name}' archivado exitosamente. Campañas y cupones pausados.";
        } else {
            $ally->couponsDirB()->where('status', 'paused')->update(['status' => 'active']);
            $msg = "Aliado '{$ally->name}' reactivado exitosamente.";
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'status' => $newStatus,
            'is_archived' => $isArchiving,
            'ally' => $ally,
        ]);
    }

    /**
     * Elimina a un aliado comercial con borrado suave (API).
     */
    public function destroyAlly(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $ally = Ally::findOrFail($id);
        $allyName = $ally->name;

        $ally->campaignsDirA()->where('status', 'active')->update(['status' => 'ended']);
        $ally->couponsDirB()->where('status', 'active')->update(['status' => 'paused']);
        $ally->update(['status' => 'archived']);

        $ally->delete();

        return response()->json([
            'success' => true,
            'message' => "Aliado '{$allyName}' eliminado del directorio oficial.",
        ]);
    }

    /**
     * =========================================================================
     * ENDPOINTS API REST: TIPOS DE ALIADO / CATEGORÍAS
     * =========================================================================
     */

    public function getAllyTypes(Request $request): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $includeArchived = $request->boolean('include_archived', true);
        $query = AllyType::query();

        if ($includeArchived) {
            $query->withTrashed();
        } else {
            $query->active();
        }

        $types = $query->withCount('allies')->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function storeAllyType(Request $request): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'default_universe' => 'nullable|string|in:marte,venus,tiresias,terra',
            'is_active' => 'nullable|boolean',
        ]);

        $name = trim($validated['name']);
        $code = !empty($validated['code'])
            ? Str::slug($validated['code'], '_')
            : Str::slug($name, '_');

        if (AllyType::withTrashed()->where('code', $code)->exists()) {
            return response()->json(['success' => false, 'message' => "El código '{$code}' ya existe."], 422);
        }

        $type = AllyType::create([
            'name' => $name,
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'default_universe' => $validated['default_universe'] ?? 'marte',
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de aliado '{$type->name}' registrado exitosamente.",
            'data' => $type,
        ], 201);
    }

    public function updateAllyType(Request $request, int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $type = AllyType::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'default_universe' => 'nullable|string|in:marte,venus,tiresias,terra',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,archived',
        ]);

        $name = trim($validated['name']);
        $code = !empty($validated['code']) ? Str::slug($validated['code'], '_') : $type->code;

        $conflict = AllyType::withTrashed()->where('code', $code)->where('id', '!=', $type->id)->exists();
        if ($conflict) {
            return response()->json(['success' => false, 'message' => "El código '{$code}' ya está en uso."], 422);
        }

        $type->update([
            'name' => $name,
            'code' => $code,
            'description' => $validated['description'] ?? $type->description,
            'default_universe' => $validated['default_universe'] ?? $type->default_universe,
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : $type->is_active,
            'status' => $validated['status'] ?? $type->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de aliado '{$type->name}' actualizado correctamente.",
            'data' => $type,
        ]);
    }

    public function toggleAllyTypeActive(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $type = AllyType::withTrashed()->findOrFail($id);
        $type->update(['is_active' => !$type->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de aliado '{$type->name}' " . ($type->is_active ? 'activado' : 'desactivado') . ".",
            'is_active' => $type->is_active,
        ]);
    }

    public function archiveAllyType(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $type = AllyType::withTrashed()->findOrFail($id);
        $isArchiving = ($type->status === 'active');
        $newStatus = $isArchiving ? 'archived' : 'active';
        $type->update([
            'status' => $newStatus,
            'is_active' => !$isArchiving,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de aliado '{$type->name}' " . ($isArchiving ? 'archivado' : 'reactivado') . ".",
            'status' => $newStatus,
            'is_active' => $type->is_active,
        ]);
    }

    public function destroyAllyType(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $type = AllyType::withTrashed()->findOrFail($id);
        $name = $type->name;

        if (Ally::where('category', $type->code)->exists()) {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar el tipo '{$name}' porque tiene aliados vinculados.",
            ], 422);
        }

        $type->delete();

        return response()->json([
            'success' => true,
            'message' => "Tipo de aliado '{$name}' eliminado del catálogo.",
        ]);
    }

    /**
     * =========================================================================
     * ENDPOINTS API REST: TIPOS DE DESCUENTO DIR A & DIR B
     * =========================================================================
     */

    public function getDiscountTypes(Request $request): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $includeArchived = $request->boolean('include_archived', true);
        $direction = $request->input('direction');

        $query = AllyDiscountType::query();

        if ($includeArchived) {
            $query->withTrashed();
        } else {
            $query->active();
        }

        if ($direction === 'dir_a') {
            $query->dirA();
        } elseif ($direction === 'dir_b') {
            $query->dirB();
        }

        $discounts = $query->get();

        return response()->json([
            'success' => true,
            'data' => $discounts,
        ]);
    }

    public function storeDiscountType(Request $request): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'direction' => 'required|string|in:dir_a,dir_b,both',
            'value_type' => 'required|string|in:percentage,fixed_amount,free_item,access',
            'max_value_default' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $name = trim($validated['name']);
        $code = !empty($validated['code'])
            ? Str::slug($validated['code'], '_')
            : Str::slug($name, '_');

        if (AllyDiscountType::withTrashed()->where('code', $code)->exists()) {
            return response()->json(['success' => false, 'message' => "El código de descuento '{$code}' ya existe."], 422);
        }

        $discount = AllyDiscountType::create([
            'name' => $name,
            'code' => $code,
            'direction' => $validated['direction'],
            'value_type' => $validated['value_type'],
            'max_value_default' => isset($validated['max_value_default']) ? (float)$validated['max_value_default'] : null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de descuento '{$discount->name}' registrado exitosamente.",
            'data' => $discount,
        ], 201);
    }

    public function updateDiscountType(Request $request, int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $discount = AllyDiscountType::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'direction' => 'required|string|in:dir_a,dir_b,both',
            'value_type' => 'required|string|in:percentage,fixed_amount,free_item,access',
            'max_value_default' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,archived',
        ]);

        $name = trim($validated['name']);
        $code = !empty($validated['code']) ? Str::slug($validated['code'], '_') : $discount->code;

        $conflict = AllyDiscountType::withTrashed()->where('code', $code)->where('id', '!=', $discount->id)->exists();
        if ($conflict) {
            return response()->json(['success' => false, 'message' => "El código '{$code}' ya está en uso."], 422);
        }

        $discount->update([
            'name' => $name,
            'code' => $code,
            'direction' => $validated['direction'],
            'value_type' => $validated['value_type'],
            'max_value_default' => isset($validated['max_value_default']) ? (float)$validated['max_value_default'] : $discount->max_value_default,
            'description' => $validated['description'] ?? $discount->description,
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : $discount->is_active,
            'status' => $validated['status'] ?? $discount->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de descuento '{$discount->name}' actualizado correctamente.",
            'data' => $discount,
        ]);
    }

    public function toggleDiscountTypeActive(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $discount = AllyDiscountType::withTrashed()->findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de descuento '{$discount->name}' " . ($discount->is_active ? 'activado' : 'desactivado') . ".",
            'is_active' => $discount->is_active,
        ]);
    }

    public function archiveDiscountType(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $discount = AllyDiscountType::withTrashed()->findOrFail($id);
        $isArchiving = ($discount->status === 'active');
        $newStatus = $isArchiving ? 'archived' : 'active';
        $discount->update([
            'status' => $newStatus,
            'is_active' => !$isArchiving,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tipo de descuento '{$discount->name}' " . ($isArchiving ? 'archivado' : 'reactivado') . ".",
            'status' => $newStatus,
            'is_active' => $discount->is_active,
        ]);
    }

    public function destroyDiscountType(int $id): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $discount = AllyDiscountType::withTrashed()->findOrFail($id);
        $name = $discount->name;

        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => "Tipo de descuento '{$name}' eliminado del catálogo.",
        ]);
    }
}
