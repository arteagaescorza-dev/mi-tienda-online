<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\ScrapedPendingItem;
use App\Models\CycleCount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InboundReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryApiController extends Controller
{
    protected InboundReceiptService $inboundService;

    public function __construct(InboundReceiptService $inboundService)
    {
        $this->inboundService = $inboundService;
    }

    /**
     * Procesa la recepción física Inbound de una Orden de Compra (TASK-INV-04).
     */
    public function receivePurchaseOrder(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $po = PurchaseOrder::with('items.variant')->findOrFail($id);
        $result = $this->inboundService->receivePurchaseOrder(
            $po,
            $validated['items'] ?? [],
            auth()->id(),
            $validated['notes'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Aprueba o rechaza un ítem extraído por el Scraper de Torzo (TASK-INV-08).
     */
    public function curateScrapedItem(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'curated_title' => 'required_if:action,approve|string|max:200',
            'curated_brand' => 'nullable|string|max:80',
            'curated_fabric' => 'nullable|string|max:80',
            'curated_color' => 'nullable|string|max:80',
            'curated_design_type' => 'nullable|string|in:sin_diseno,con_diseno',
            'curated_website_section' => 'nullable|string|in:basicos,disenador,temporada,general',
            'curated_price' => 'required_if:action,approve|numeric|min:0',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ]);

        $item = ScrapedPendingItem::findOrFail($id);

        if ($validated['action'] === 'approve') {
            $item->curated_title = $validated['curated_title'];
            $item->curated_brand = $validated['curated_brand'] ?? 'Torzo';
            $item->curated_fabric = $validated['curated_fabric'] ?? 'Algodón Pima';
            $item->curated_color = $validated['curated_color'] ?? 'Negro Ónix';
            $item->curated_design_type = $validated['curated_design_type'] ?? 'sin_diseno';
            $item->curated_website_section = $validated['curated_website_section'] ?? 'basicos';
            $item->curated_price = $validated['curated_price'];
            $item->curation_status = 'approved';
            $item->save();

            // Insertar o sincronizar en catálogo de productos
            $product = Product::firstOrCreate(
                ['sku' => $item->external_sku ?: 'SKU-' . uniqid()],
                [
                    'title' => $item->curated_title,
                    'slug' => \Illuminate\Support\Str::slug($item->curated_title),
                    'brand' => $item->curated_brand,
                    'type' => 'dropship',
                    'price' => $item->curated_price,
                    'cost' => $item->external_price,
                    'visible' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Producto aprobado e insertado en catálogo maestro',
                'product_id' => $product->id,
                'status' => 'approved',
            ]);
        }

        $item->curation_status = 'rejected';
        $item->rejection_reason = $validated['rejection_reason'] ?? 'Descartado por operador';
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Ítem descartado de la bandeja de curaduría',
            'status' => 'rejected',
        ]);
    }

    /**
     * Registra el resultado de una auditoría cíclica a ciegas (TASK-INV-07).
     */
    public function recordCycleCount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'counts' => 'required|array|min:1',
            'counts.*.variant_id' => 'required|integer',
            'counts.*.physical_counted' => 'required|integer|min:0',
        ]);

        $totalCounted = 0;
        $totalVariance = 0;
        $details = [];

        foreach ($validated['counts'] as $countData) {
            $variant = ProductVariant::with('inventory')->find($countData['variant_id']);
            if ($variant) {
                $systemStock = $variant->inventory ? $variant->inventory->quantity : 0;
                $physical = $countData['physical_counted'];
                $variance = $physical - $systemStock;

                $totalCounted += $physical;
                $totalVariance += abs($variance);

                $details[] = [
                    'variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'system_stock' => $systemStock,
                    'physical_counted' => $physical,
                    'variance' => $variance,
                ];
            }
        }

        $totalTheoretical = $totalCounted + $totalVariance;
        $iraScore = $totalTheoretical > 0 
            ? max(0, min(100, round((1 - ($totalVariance / $totalTheoretical)) * 100, 2))) 
            : 100.00;

        $cycleCount = CycleCount::create([
            'count_date' => now()->toDateString(),
            'sample_sku_count' => count($details),
            'total_units_counted' => $totalCounted,
            'total_variance_units' => $totalVariance,
            'ira_accuracy_score' => $iraScore,
            'audited_by' => auth()->user() ? auth()->user()->name : 'Operador Almacén',
            'status' => $totalVariance === 0 ? 'reconciled' : 'flagged',
            'details_json' => $details,
        ]);

        return response()->json([
            'success' => true,
            'cycle_count_id' => $cycleCount->id,
            'ira_accuracy_score' => $iraScore,
            'status' => $cycleCount->status,
            'details' => $details,
        ]);
    }
}
