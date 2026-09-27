<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseKardex;
use App\Models\Supplier;
use App\Models\SupplierCreditAgreement;
use App\Models\ScrapedPendingItem;
use App\Models\CycleCount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InboundReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    protected InboundReceiptService $inboundService;

    public function __construct(InboundReceiptService $inboundService)
    {
        $this->inboundService = $inboundService;
    }

    /**
     * Consulta acuerdos y notas de credito de proveedores pendientes (TASK-PUR-01).
     */
    public function getCreditAgreements(Request $request): JsonResponse
    {
        $query = SupplierCreditAgreement::query()->with('supplier');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        } else {
            $query->where('status', 'pending');
        }

        $agreements = $query->latest()->get()->map(fn ($ca) => [
            'id' => $ca->id,
            'supplier_id' => $ca->supplier_id,
            'supplier' => $ca->supplier?->name ?? 'Proveedor',
            'ref' => $ca->reference_folio ?: ('NC-' . $ca->id),
            'amount' => (float) $ca->amount,
            'reason' => $ca->resolution_notes ?: 'Acuerdo compensatorio por discrepancia previa',
            'agreement_type' => $ca->agreement_type,
            'status' => $ca->status === 'pending' ? 'Pendiente de Aplicar' : 'Aplicada',
            'date' => $ca->created_at ? $ca->created_at->format('d/m/Y') : now()->format('d/m/Y'),
        ]);

        return response()->json([
            'success' => true,
            'agreements' => $agreements,
        ]);
    }

    /**
     * Emite formalmente una Orden de Compra con deduccion de notas de credito y Kardex (TASK-INV-16 / TASK-PUR-04).
     */
    public function storePurchaseOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'po_number' => 'nullable|string|max:50',
            'supplier_id' => 'nullable|integer',
            'supplier' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
            'delivery_commitment_date' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'has_credit_deduction' => 'nullable|boolean',
            'credit_agreement_id' => 'nullable|integer',
            'credit_note_ref' => 'nullable|string|max:50',
            'credit_note_amount' => 'nullable|numeric|min:0',
            'credit_note_reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.product_name' => 'nullable|string|max:200',
            'items.*.qty' => 'nullable|integer|min:0',
            'items.*.cost_unit' => 'nullable|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.sizes' => 'nullable|array',
            'items.*.priority' => 'nullable|string|max:30',
            'po_snapshot' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Resolver o asociar Proveedor
            $supplier = null;
            if (!empty($validated['supplier_id'])) {
                $supplier = Supplier::find($validated['supplier_id']);
            }
            if (!$supplier && !empty($validated['supplier'])) {
                $cleanName = trim($validated['supplier']);
                if (strtolower($cleanName) !== 'consolidado multimarca') {
                    $supplier = Supplier::firstOrCreate(['name' => $cleanName]);
                } else {
                    $supplier = Supplier::first();
                }
            }
            if (!$supplier) {
                $supplier = Supplier::first() ?? Supplier::create(['name' => 'Torzo B2B']);
            }

            // 2. Generar Folio Unico
            $rawPoNum = $validated['po_number'] ?? null;
            if ($rawPoNum && !PurchaseOrder::where('po_number', $rawPoNum)->exists()) {
                $poNumber = $rawPoNum;
            } else {
                $poNumber = 'PO-' . date('Y') . '-' . str_pad((string) (PurchaseOrder::count() + 1001), 4, '0', STR_PAD_LEFT);
            }

            // 3. Procesar Partidas e Items
            $totalUnits = 0;
            $grossSubtotal = 0.0;
            $preparedItems = [];

            foreach ($validated['items'] as $itemData) {
                $unitCost = (float) ($itemData['unit_cost'] ?? $itemData['cost_unit'] ?? 0);
                $sku = $itemData['sku'] ?? null;
                $product = null;
                if (!empty($itemData['product_id'])) {
                    $product = Product::find($itemData['product_id']);
                } elseif ($sku) {
                    $product = Product::where('sku', $sku)->first();
                }

                $sizes = $itemData['sizes'] ?? [];
                if (!empty($sizes) && is_array($sizes)) {
                    foreach ($sizes as $sizeKey => $sizeQty) {
                        $qty = (int) $sizeQty;
                        if ($qty <= 0) continue;
                        $lineTotal = round($qty * $unitCost, 2);
                        $totalUnits += $qty;
                        $grossSubtotal += $lineTotal;

                        $variant = null;
                        if ($product) {
                            $variant = $product->variants()
                                ->whereJsonContains('attributes->size', strtoupper($sizeKey))
                                ->orWhereJsonContains('attributes->size', strtolower($sizeKey))
                                ->first();
                        }

                        $preparedItems[] = [
                            'product_id' => $product?->id,
                            'product_variant_id' => $variant?->id ?? ($itemData['variant_id'] ?? null),
                            'size_name' => strtoupper($sizeKey),
                            'quantity_ordered' => $qty,
                            'quantity_received' => 0,
                            'unit_cost' => $unitCost,
                            'total_cost' => $lineTotal,
                        ];
                    }
                } else {
                    $qty = (int) ($itemData['qty'] ?? 1);
                    if ($qty > 0) {
                        $lineTotal = round($qty * $unitCost, 2);
                        $totalUnits += $qty;
                        $grossSubtotal += $lineTotal;

                        $preparedItems[] = [
                            'product_id' => $product?->id,
                            'product_variant_id' => $itemData['variant_id'] ?? null,
                            'size_name' => 'U',
                            'quantity_ordered' => $qty,
                            'quantity_received' => 0,
                            'unit_cost' => $unitCost,
                            'total_cost' => $lineTotal,
                        ];
                    }
                }
            }

            // 4. Procesar Deduccion de Notas de Credito / Acuerdos Compensatorios
            $hasCreditDeduction = !empty($validated['has_credit_deduction']);
            $deductionAmount = 0.0;
            $appliedAgreement = null;

            if ($hasCreditDeduction) {
                if (!empty($validated['credit_agreement_id'])) {
                    $appliedAgreement = SupplierCreditAgreement::where('id', $validated['credit_agreement_id'])
                        ->where('status', 'pending')
                        ->first();
                }
                if (!$appliedAgreement && !empty($validated['credit_note_ref'])) {
                    $appliedAgreement = SupplierCreditAgreement::where('reference_folio', $validated['credit_note_ref'])
                        ->where('status', 'pending')
                        ->first();
                }

                if ($appliedAgreement) {
                    $deductionAmount = min($grossSubtotal, (float) $appliedAgreement->amount);
                    $appliedAgreement->status = 'applied';
                    $appliedAgreement->save();
                } elseif (!empty($validated['credit_note_amount'])) {
                    $deductionAmount = min($grossSubtotal, (float) $validated['credit_note_amount']);
                    $appliedAgreement = SupplierCreditAgreement::create([
                        'supplier_id' => $supplier->id,
                        'reference_folio' => $validated['credit_note_ref'] ?? ('NC-' . uniqid()),
                        'agreement_type' => 'shortage_discount',
                        'amount' => $deductionAmount,
                        'status' => 'applied',
                        'resolution_notes' => $validated['credit_note_reason'] ?? 'Compensacion aplicada en emision de OC',
                    ]);
                }
            }

            // 5. Calculos Financieros Fiscales (Neto + IVA 16%)
            $taxableSubtotal = max(0, round($grossSubtotal - $deductionAmount, 2));
            $tax = round($taxableSubtotal * 0.16, 2);
            $totalAmount = round($taxableSubtotal + $tax, 2);

            // 6. Crear la Orden de Compra Oficial
            $poNotes = $validated['notes'] ?? '';
            if ($deductionAmount > 0 && $appliedAgreement) {
                $poNotes = ($poNotes ? $poNotes . "\n" : '') . sprintf(
                    'Compensacion aplicada bajo acuerdo %s ($%.2f MXN): %s',
                    $appliedAgreement->reference_folio,
                    $deductionAmount,
                    $appliedAgreement->resolution_notes ?? 'Ajuste por discrepancia'
                );
            }

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $supplier->id,
                'status' => 'confirmed',
                'total_units' => $totalUnits,
                'subtotal' => $taxableSubtotal,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'currency' => 'MXN',
                'sent_at' => now(),
                'notes' => $poNotes,
            ]);

            if ($appliedAgreement) {
                $appliedAgreement->purchase_order_id = $po->id;
                $appliedAgreement->save();
            }

            // 7. Persistir partidas
            foreach ($preparedItems as $pi) {
                $po->items()->create($pi);
            }

            // 8. Asentamiento en Purchase Kardex (PO_CREATED)
            $kardexDesc = sprintf(
                'Emision formal de OC #%s a %s por %d unidades ($%.2f MXN)',
                $po->po_number,
                $supplier->name,
                $totalUnits,
                $totalAmount
            );
            if ($deductionAmount > 0) {
                $kardexDesc .= sprintf(' [Deduccion de NC %s: -$%.2f MXN]', $appliedAgreement?->reference_folio ?? 'NC', $deductionAmount);
            }

            PurchaseKardex::create([
                'purchase_order_id' => $po->id,
                'supplier_id' => $supplier->id,
                'movement_type' => 'PO_CREATED',
                'description' => $kardexDesc,
                'total_amount' => $totalAmount,
            ]);

            $po->load(['supplier', 'items.product', 'items.variant']);

            return response()->json([
                'success' => true,
                'message' => 'Orden de compra emitida y persistida exitosamente.',
                'order' => [
                    'id' => $po->po_number,
                    'dbId' => $po->id,
                    'poNumber' => $po->po_number,
                    'supplier' => $po->supplier?->name ?? 'Proveedor',
                    'supplier_id' => $po->supplier_id,
                    'totalPieces' => $po->total_units,
                    'subtotal' => (float) $po->subtotal,
                    'tax' => (float) $po->tax,
                    'total' => (float) $po->total_amount,
                    'totalCost' => (float) $po->total_amount,
                    'status' => 'Por Despachar',
                    'date' => $po->created_at->format('d M, Y'),
                    'deductionAmount' => $deductionAmount,
                    'creditNoteRef' => $appliedAgreement?->reference_folio,
                    'items_count' => $po->items->count(),
                ],
            ], 201);
        });
    }

    /**
     * Notifica y despacha formalmente una Orden de Compra por correo o fletera (TASK-INV-16 / TASK-PUR-01).
     */
    public function dispatchPurchaseOrder(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'recipient_email' => 'nullable|email|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        $po = PurchaseOrder::with(['supplier', 'items'])->findOrFail($id);

        $po->status = 'in_transit';
        $po->sent_at = now();

        $dispatchNote = sprintf(
            'Despacho formal notificado a %s%s%s',
            $validated['recipient_email'] ?? ($po->supplier?->contact_email ?? 'taller'),
            !empty($validated['carrier']) ? ' via ' . $validated['carrier'] : '',
            !empty($validated['tracking_number']) ? ' (Guia: ' . $validated['tracking_number'] . ')' : ''
        );

        if (!empty($validated['notes'])) {
            $dispatchNote .= ' - ' . $validated['notes'];
        }

        $po->notes = ($po->notes ? $po->notes . "\n" : '') . $dispatchNote;
        $po->save();

        PurchaseKardex::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $po->supplier_id,
            'movement_type' => 'PO_SENT',
            'description' => sprintf(
                'Despacho de OC #%s amparando %d unidades en transito (%s)',
                $po->po_number,
                $po->total_units,
                $dispatchNote
            ),
            'total_amount' => (float) $po->total_amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Orden de compra despachada y asentada en Kardex.',
            'po_id' => $po->id,
            'po_number' => $po->po_number,
            'status' => 'En Camino',
            'sent_at' => $po->sent_at->toIso8601String(),
        ]);
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
