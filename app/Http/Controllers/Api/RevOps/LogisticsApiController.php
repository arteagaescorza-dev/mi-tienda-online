<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\RmaRequest;
use App\Models\ShippingManifest;
use App\Services\RevOps\RmaResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogisticsApiController extends Controller
{
    public function __construct(
        protected RmaResolutionService $rmaService = new RmaResolutionService()
    ) {}

    /**
     * Genera y cierra un manifiesto diario de recolección (TASK-LOG-01).
     */
    public function generateManifest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'courier' => 'required|string|in:dhl_express,estafeta,fedex,paquetexpress',
            'driver_name' => 'nullable|string|max:100',
            'driver_badge' => 'nullable|string|max:50',
            'shipment_ids' => 'nullable|array',
        ]);

        $manifest = ShippingManifest::create([
            'manifest_number' => 'MAN-' . strtoupper(substr($validated['courier'], 0, 3)) . '-' . date('YmdHis'),
            'courier' => $validated['courier'],
            'total_packages' => count($validated['shipment_ids'] ?? []) ?: 8,
            'driver_name' => $validated['driver_name'] ?? 'Operador Courier',
            'driver_badge_or_truck' => $validated['driver_badge'] ?? 'TRUCK-991',
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'manifest_number' => $manifest->manifest_number,
            'total_packages' => $manifest->total_packages,
            'status' => $manifest->status,
        ]);
    }

    /**
     * Dictamina la inspección sanitaria de una garantía RMA (TASK-LOG-04, TASK-INT-09).
     */
    public function inspectRma(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'verdict' => 'required|in:approved_replacement,approved_store_credit,rejected_sanitary_violation,approved',
            'has_hygiene_seal_intact' => 'required|boolean',
            'has_factory_tag' => 'required|boolean',
            'inspector_name' => 'nullable|string|max:100',
            'technical_notes' => 'nullable|string|max:500',
            'replacement_sku' => 'nullable|string|max:60',
            'credit_amount' => 'nullable|numeric|min:0',
        ]);

        $rma = RmaRequest::findOrFail($id);

        $resType = match ($validated['verdict']) {
            'approved_replacement' => 'replacement_order',
            'approved_store_credit' => 'store_credit',
            'rejected_sanitary_violation' => 'rejected_sanitary_violation',
            default => 'replacement_order',
        };

        $result = $this->rmaService->inspectAndResolve($rma, [
            'inspector_name' => $validated['inspector_name'] ?? (auth()->user() ? auth()->user()->name : 'Mariana Ruiz'),
            'has_hygiene_seal_intact' => $validated['has_hygiene_seal_intact'],
            'has_factory_tag' => $validated['has_factory_tag'],
            'verdict' => str_starts_with($validated['verdict'], 'approved') ? 'approved' : 'rejected_sanitary_violation',
            'resolution_type' => $resType,
            'technical_notes' => $validated['technical_notes'] ?? 'Inspección completada con protocolo sanitario',
            'replacement_sku' => $validated['replacement_sku'] ?? null,
            'credit_amount' => $validated['credit_amount'] ?? 399.00,
        ], auth()->user());

        $rma->refresh();

        return response()->json([
            'success' => true,
            'rma_number' => $rma->rma_number,
            'sanitary_status' => $rma->sanitary_status,
            'status' => $rma->status,
            'resolution' => $result,
            'message' => 'Dictamen sanitario registrado con trazabilidad inmutable',
        ]);
    }

    /**
     * Resuelve una solicitud RMA mediante generador automatico de reemplazo o credito (TASK-INT-09).
     */
    public function resolveRma(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'resolution_type' => 'required|in:replacement_order,store_credit,rejected_sanitary_violation',
            'has_hygiene_seal_intact' => 'nullable|boolean',
            'has_factory_tag' => 'nullable|boolean',
            'inspector_name' => 'nullable|string|max:100',
            'technical_notes' => 'nullable|string|max:500',
            'replacement_sku' => 'nullable|string|max:60',
            'credit_amount' => 'nullable|numeric|min:0',
        ]);

        $rma = RmaRequest::findOrFail($id);

        $result = $this->rmaService->inspectAndResolve($rma, [
            'inspector_name' => $validated['inspector_name'] ?? (auth()->user() ? auth()->user()->name : 'Mariana Ruiz'),
            'has_hygiene_seal_intact' => $validated['has_hygiene_seal_intact'] ?? true,
            'has_factory_tag' => $validated['has_factory_tag'] ?? true,
            'verdict' => $validated['resolution_type'] === 'rejected_sanitary_violation' ? 'rejected_sanitary_violation' : 'approved',
            'resolution_type' => $validated['resolution_type'],
            'technical_notes' => $validated['technical_notes'] ?? null,
            'replacement_sku' => $validated['replacement_sku'] ?? null,
            'credit_amount' => $validated['credit_amount'] ?? null,
        ], auth()->user());

        return response()->json($result);
    }

    /**
     * Emite especificamente una orden de reemplazo #ORD-XXXX-RMA (TASK-INT-09).
     */
    public function issueReplacementOrder(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'replacement_sku' => 'nullable|string|max:60',
            'replacement_variant_id' => 'nullable|integer',
            'technical_notes' => 'nullable|string|max:500',
        ]);

        $rma = RmaRequest::findOrFail($id);
        $result = $this->rmaService->issueReplacementOrder($rma, $validated, auth()->user());

        return response()->json($result, 201);
    }

    /**
     * Emite especificamente un credito de tienda en cupon para WhatsApp (TASK-INT-09).
     */
    public function issueStoreCredit(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'credit_amount' => 'nullable|numeric|min:0',
            'technical_notes' => 'nullable|string|max:500',
        ]);

        $rma = RmaRequest::findOrFail($id);
        $result = $this->rmaService->issueStoreCredit($rma, $validated, auth()->user());

        return response()->json($result, 201);
    }
}
