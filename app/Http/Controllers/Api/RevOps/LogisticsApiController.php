<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\RmaRequest;
use App\Models\ShippingManifest;
use App\Services\RevOps\RmaResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RevOps\GenerateManifestRequest;
use App\Http\Requests\Api\RevOps\InspectRmaRequest;
use App\Http\Requests\Api\RevOps\ResolveRmaRequest;
use App\Http\Requests\Api\RevOps\IssueReplacementOrderRequest;
use App\Http\Requests\Api\RevOps\IssueStoreCreditRequest;
use Illuminate\Support\Facades\Gate;


class LogisticsApiController extends Controller
{
    public function __construct(
        protected RmaResolutionService $rmaService = new RmaResolutionService()
    ) {}

    /**
     * Genera y cierra un manifiesto diario de recolección (TASK-LOG-01).
     */
    public function generateManifest(GenerateManifestRequest $request): JsonResponse
    {
        Gate::authorize('manage-logistics');

        $validated = $request->validated();

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
    public function inspectRma(InspectRmaRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-logistics');

        $validated = $request->validated();

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
    public function resolveRma(ResolveRmaRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-logistics');

        $validated = $request->validated();

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
    public function issueReplacementOrder(IssueReplacementOrderRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-logistics');

        $validated = $request->validated();

        $rma = RmaRequest::findOrFail($id);
        $result = $this->rmaService->issueReplacementOrder($rma, $validated, auth()->user());

        return response()->json($result, 201);
    }

    /**
     * Emite especificamente un credito de tienda en cupon para WhatsApp (TASK-INT-09).
     */
    public function issueStoreCredit(IssueStoreCreditRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-logistics');

        $validated = $request->validated();

        $rma = RmaRequest::findOrFail($id);
        $result = $this->rmaService->issueStoreCredit($rma, $validated, auth()->user());

        return response()->json($result, 201);
    }
}
