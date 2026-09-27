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
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBundle;
use App\Models\BundleItem;
use App\Models\BomDeficitProcurement;
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
            'batch_number' => 'nullable|string|max:80',
            'expiration_date' => 'nullable|string|max:30',
            'pao_months' => 'nullable|integer|min:0|max:120',
            'sanitary_data' => 'nullable|array',
        ]);

        $po = PurchaseOrder::with('items.variant')->findOrFail($id);

        $sanitaryPayload = [
            'batch_number' => $validated['batch_number'] ?? null,
            'expiration_date' => $validated['expiration_date'] ?? null,
            'pao_months' => $validated['pao_months'] ?? null,
            'sanitary_data' => $validated['sanitary_data'] ?? [],
        ];

        $result = $this->inboundService->receivePurchaseOrder(
            $po,
            $validated['items'] ?? [],
            auth()->id(),
            $validated['notes'] ?? null,
            $sanitaryPayload
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
            'counts.*.variant_id' => 'nullable|integer',
            'counts.*.sku' => 'nullable|string|max:100',
            'counts.*.physical_counted' => 'required|integer|min:0',
            'counts.*.reason' => 'nullable|string|max:255',
            'reconcile_kardex' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $totalCounted = 0;
            $totalSystem = 0;
            $totalVariance = 0;
            $details = [];
            $shouldReconcile = $request->boolean('reconcile_kardex');

            foreach ($validated['counts'] as $countData) {
                $variant = null;
                $sku = $countData['sku'] ?? null;
                $variantId = $countData['variant_id'] ?? null;

                if ($variantId) {
                    $variant = ProductVariant::with(['inventory', 'product'])->find($variantId);
                }
                if (!$variant && $sku) {
                    $variant = ProductVariant::with(['inventory', 'product'])->where('sku', $sku)->first();
                    if (!$variant) {
                        $product = Product::with(['variants.inventories'])->where('sku', $sku)->first();
                        $variant = $product?->variants?->first();
                    }
                }

                if ($variant) {
                    $inventory = Inventory::firstOrCreate(
                        ['variant_id' => $variant->id, 'location' => 'warehouse-1'],
                        ['quantity' => 0, 'reserved' => 0, 'type' => 'physical']
                    );

                    $systemStock = (int) $inventory->quantity;
                    $physical = (int) $countData['physical_counted'];
                    $variance = $physical - $systemStock;

                    $totalCounted += $physical;
                    $totalSystem += $systemStock;
                    $totalVariance += abs($variance);

                    if ($shouldReconcile && $variance !== 0) {
                        $prevQty = $inventory->quantity;
                        $inventory->quantity = $physical;
                        $inventory->save();

                        $reasonText = sprintf(
                            'AJUSTE_AUDITORIA_CIEGA - SKU: %s | Discrepancia: %s%d uds | %s',
                            $variant->sku,
                            $variance > 0 ? '+' : '',
                            $variance,
                            $countData['reason'] ?? 'Merma operativa'
                        );
                        if (strlen($reasonText) > 250) {
                            $reasonText = substr($reasonText, 0, 247) . '...';
                        }

                        InventoryMovement::create([
                            'inventory_id' => $inventory->id,
                            'user_id' => auth()->id(),
                            'quantity' => $variance,
                            'quantity_before' => $prevQty,
                            'quantity_after' => $physical,
                            'reason' => $reasonText,
                        ]);
                    }

                    $details[] = [
                        'variant_id' => $variant->id,
                        'sku' => $variant->sku,
                        'product_name' => $variant->product?->title ?? $variant->sku,
                        'system_stock' => $systemStock,
                        'physical_counted' => $physical,
                        'variance' => $variance,
                        'reason' => $countData['reason'] ?? 'Merma operativa',
                    ];
                }
            }

            $totalTheoretical = $totalCounted + $totalVariance;
            $iraScore = $totalTheoretical > 0 
                ? max(0, min(100, round((1 - ($totalVariance / $totalTheoretical)) * 100, 2))) 
                : 100.00;

            $status = ($totalVariance === 0 || $shouldReconcile) ? 'reconciled' : 'flagged';

            $cycleCount = CycleCount::create([
                'count_date' => now()->toDateString(),
                'sample_sku_count' => count($details),
                'total_units_counted' => $totalCounted,
                'total_variance_units' => $totalVariance,
                'ira_accuracy_score' => $iraScore,
                'audited_by' => auth()->user() ? auth()->user()->name : 'Operador Almacén',
                'status' => $status,
                'details_json' => $details,
            ]);

            return response()->json([
                'success' => true,
                'cycle_count_id' => $cycleCount->id,
                'ira_accuracy_score' => $iraScore,
                'status' => $cycleCount->status,
                'total_units_counted' => $totalCounted,
                'total_variance_units' => $totalVariance,
                'details' => $details,
            ]);
        });
    }

    /**
     * Lista formulas BOM y combos activos calculando capacidad de ensamble por cuello de botella (TASK-INV-17).
     */
    public function getBundles(Request $request): JsonResponse
    {
        $bundles = ProductBundle::with(['items.product.variants.inventories', 'items.variant.inventories'])
            ->get()
            ->map(function ($bundle) {
                $bottleneckCapacity = null;
                $components = [];

                foreach ($bundle->items as $item) {
                    $product = $item->product;
                    $variant = $item->variant;
                    $qtyPerBundle = max(1, (int) $item->quantity);

                    if ($variant) {
                        $available = (int) $variant->inventories->sum(fn ($inv) => max(0, $inv->quantity - $inv->reserved));
                        $size = data_get($variant->attributes, 'size', 'M');
                    } elseif ($product) {
                        $available = (int) $product->variants->sum(function ($v) {
                            return $v->inventories->sum(fn ($inv) => max(0, $inv->quantity - $inv->reserved));
                        });
                        $size = 'M';
                    } else {
                        $available = 0;
                        $size = 'M';
                    }

                    $capacityForComp = (int) floor($available / $qtyPerBundle);
                    if ($bottleneckCapacity === null || $capacityForComp < $bottleneckCapacity) {
                        $bottleneckCapacity = $capacityForComp;
                    }

                    $components[] = [
                        'item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->product_variant_id,
                        'sku' => $variant?->sku ?? $product?->sku ?? 'SKU-COMP',
                        'name' => $product?->title ?? 'Prenda Componente',
                        'size' => strtolower((string) $size),
                        'qty' => $qtyPerBundle,
                        'available_stock' => $available,
                        'component_capacity' => $capacityForComp,
                    ];
                }

                if ($bottleneckCapacity === null) {
                    $bottleneckCapacity = 0;
                }

                return [
                    'id' => $bundle->id,
                    'sku' => $bundle->slug,
                    'name' => $bundle->name,
                    'slug' => $bundle->slug,
                    'bundle_type' => $bundle->bundle_type,
                    'type' => $bundle->bundle_type === 'box_subscription' ? 'subscription' : 'promo',
                    'regular_price' => (float) $bundle->regular_price,
                    'bundle_price' => (float) $bundle->bundle_price,
                    'subscription_price' => (float) $bundle->bundle_price,
                    'is_active' => (bool) $bundle->is_active,
                    'bottleneck_capacity' => $bottleneckCapacity,
                    'components' => $components,
                ];
            });

        return response()->json([
            'success' => true,
            'bundles' => $bundles,
        ]);
    }

    /**
     * Guarda o actualiza una receta BOM de ensamble con sus componentes (TASK-INV-03 / TASK-INV-17).
     */
    public function storeBundle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable',
            'bundle_id' => 'nullable',
            'sku' => 'required|string|max:150',
            'name' => 'required|string|max:150',
            'type' => 'nullable|string|max:40',
            'bundle_type' => 'nullable|string|max:40',
            'subscription_price' => 'nullable|numeric|min:0',
            'subscriptionPrice' => 'nullable|numeric|min:0',
            'bundle_price' => 'nullable|numeric|min:0',
            'regular_price' => 'nullable|numeric|min:0',
            'regularPrice' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'isActive' => 'nullable|boolean',
            'components' => 'required|array|min:1',
            'components.*.sku' => 'required|string|max:100',
            'components.*.size' => 'nullable|string|max:20',
            'components.*.qty' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {
            $cleanSku = strtoupper(trim($validated['sku']));
            $cleanName = trim($validated['name']);

            $rawType = $validated['bundle_type'] ?? $validated['type'] ?? 'subscription';
            $bundleType = match ($rawType) {
                'subscription', 'box_subscription' => 'box_subscription',
                'promo', 'pack_multiple' => 'pack_multiple',
                default => 'combo_cross',
            };

            $bundlePrice = (float) ($validated['bundle_price'] ?? $validated['subscription_price'] ?? $validated['subscriptionPrice'] ?? 0);
            $regularPrice = (float) ($validated['regular_price'] ?? $validated['regularPrice'] ?? $bundlePrice);
            $isActive = $validated['is_active'] ?? $validated['isActive'] ?? true;

            $bundle = null;
            $idCandidate = $validated['id'] ?? $validated['bundle_id'] ?? null;
            if ($idCandidate && is_numeric($idCandidate)) {
                $bundle = ProductBundle::find($idCandidate);
            }
            if (!$bundle) {
                $bundle = ProductBundle::where('slug', $cleanSku)->first();
            }
            if (!$bundle) {
                $bundle = new ProductBundle();
                $bundle->slug = $cleanSku;
            }

            $bundle->name = $cleanName;
            $bundle->slug = $cleanSku;
            $bundle->bundle_type = $bundleType;
            $bundle->bundle_price = $bundlePrice;
            $bundle->regular_price = $regularPrice;
            $bundle->is_active = (bool) $isActive;
            $bundle->save();

            $bundle->items()->delete();

            foreach ($validated['components'] as $compData) {
                $compSku = trim($compData['sku']);
                $compSize = !empty($compData['size']) ? trim($compData['size']) : 'm';
                $qty = (int) $compData['qty'];

                $product = Product::where('sku', $compSku)->first();
                if (!$product) {
                    $product = Product::firstOrCreate(
                        ['sku' => $compSku],
                        [
                            'title' => $compData['name'] ?? ('Prenda ' . $compSku),
                            'slug' => \Illuminate\Support\Str::slug($compSku . '-' . uniqid()),
                            'brand' => 'Torzo',
                            'type' => 'dropship',
                            'price' => 299.00,
                            'cost' => 120.00,
                            'visible' => true,
                        ]
                    );
                }

                $variant = ProductVariant::where('product_id', $product->id)
                    ->where(function ($q) use ($compSize) {
                        $q->where('attributes->size', $compSize)
                          ->orWhere('attributes->size', strtoupper($compSize))
                          ->orWhere('sku', 'like', "%-{$compSize}");
                    })
                    ->first();

                BundleItem::create([
                    'product_bundle_id' => $bundle->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $qty,
                ]);
            }

            $bundle->load(['items.product', 'items.variant']);

            return response()->json([
                'success' => true,
                'message' => 'Receta BOM guardada exitosamente',
                'bundle' => [
                    'id' => $bundle->id,
                    'sku' => $bundle->slug,
                    'name' => $bundle->name,
                    'type' => $bundle->bundle_type === 'box_subscription' ? 'subscription' : 'promo',
                    'subscriptionPrice' => (float) $bundle->bundle_price,
                    'regularPrice' => (float) $bundle->regular_price,
                    'isActive' => (bool) $bundle->is_active,
                    'components_count' => $bundle->items->count(),
                ],
            ], 201);
        });
    }

    /**
     * Audita déficit de insumos BOM contra un lote objetivo y dispara borradores de OC (TASK-INT-11).
     */
    public function auditBomDeficit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bundle_id' => 'nullable',
            'bundle_sku' => 'nullable|string',
            'target_units' => 'nullable|integer|min:1',
            'target_qty' => 'nullable|integer|min:1',
            'batch_reference' => 'nullable|string|max:100',
        ]);

        $targetUnits = (int) ($validated['target_units'] ?? $validated['target_qty'] ?? 1);
        $bundleIdentifier = $validated['bundle_id'] ?? $validated['bundle_sku'] ?? null;

        if (!$bundleIdentifier) {
            return response()->json([
                'success' => false,
                'message' => 'Debes especificar el ID o SKU de la receta BOM',
            ], 422);
        }

        $bundle = is_numeric($bundleIdentifier) 
            ? ProductBundle::with(['items.product.variants.inventories', 'items.variant.inventories'])->find($bundleIdentifier)
            : ProductBundle::with(['items.product.variants.inventories', 'items.variant.inventories'])->where('slug', $bundleIdentifier)->first();

        if (!$bundle) {
            return response()->json([
                'success' => false,
                'message' => 'Receta BOM no encontrada en catalogo',
            ], 404);
        }

        return DB::transaction(function () use ($bundle, $targetUnits, $validated) {
            $componentsAnalysis = [];
            $supplierGroups = [];
            $totalRequiredPieces = 0;
            $totalAvailablePieces = 0;
            $totalDeficitPieces = 0;

            $batchRef = $validated['batch_reference'] ?? ('SUB-BATCH-' . date('Ymd'));
            $procurementCode = 'BOM-PROC-' . date('Ym') . '-' . rand(100, 999);

            $defaultSupplier = Supplier::first() ?? Supplier::create(['name' => 'Torzo Labs & Textile Co.']);

            foreach ($bundle->items as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $qtyPerBox = max(1, (int) $item->quantity);
                $requiredQty = $qtyPerBox * $targetUnits;

                if ($variant) {
                    $available = (int) $variant->inventories->sum(fn ($inv) => max(0, $inv->quantity - $inv->reserved));
                    $sizeName = data_get($variant->attributes, 'size', 'M');
                } elseif ($product) {
                    $available = (int) $product->variants->sum(function ($v) {
                        return $v->inventories->sum(fn ($inv) => max(0, $inv->quantity - $inv->reserved));
                    });
                    $sizeName = 'M';
                } else {
                    $available = 0;
                    $sizeName = 'M';
                }

                $deficitQty = max(0, $requiredQty - $available);
                $isCovered = ($deficitQty === 0);
                $unitCost = (float) ($product?->cost ?? 135.00);

                $totalRequiredPieces += $requiredQty;
                $totalAvailablePieces += min($available, $requiredQty);
                $totalDeficitPieces += $deficitQty;

                $supplier = $product?->supplierProducts?->first()?->supplier ?? $defaultSupplier;

                $itemDetail = [
                    'item_id' => $item->id,
                    'product_id' => $product?->id,
                    'variant_id' => $variant?->id,
                    'sku' => $variant?->sku ?? $product?->sku ?? 'SKU-COMP',
                    'name' => $product?->title ?? 'Prenda Componente',
                    'size' => $sizeName,
                    'qty_per_box' => $qtyPerBox,
                    'required_qty' => $requiredQty,
                    'available_stock' => $available,
                    'deficit_qty' => $deficitQty,
                    'is_covered' => $isCovered,
                    'unit_cost' => $unitCost,
                    'estimated_deficit_cost' => round($deficitQty * $unitCost, 2),
                    'supplier_id' => $supplier->id,
                    'supplier_name' => $supplier->name,
                ];

                $componentsAnalysis[] = $itemDetail;

                if ($deficitQty > 0) {
                    if (!isset($supplierGroups[$supplier->id])) {
                        $supplierGroups[$supplier->id] = [
                            'supplier' => $supplier,
                            'items' => [],
                            'total_deficit_units' => 0,
                            'total_estimated_cost' => 0.0,
                        ];
                    }
                    $supplierGroups[$supplier->id]['items'][] = $itemDetail;
                    $supplierGroups[$supplier->id]['total_deficit_units'] += $deficitQty;
                    $supplierGroups[$supplier->id]['total_estimated_cost'] += ($deficitQty * $unitCost);

                    BomDeficitProcurement::create([
                        'bundle_id' => $bundle->id,
                        'component_product_id' => $product ? $product->id : 1,
                        'deficit_units' => $deficitQty,
                        'status' => 'detected',
                    ]);
                }
            }

            $createdPurchaseOrders = [];

            foreach ($supplierGroups as $supplierId => $group) {
                $poCode = 'OC-2026-BOM-' . rand(100, 999);
                while (PurchaseOrder::where('po_number', $poCode)->exists()) {
                    $poCode = 'OC-2026-BOM-' . rand(1000, 9999);
                }

                $subtotal = round($group['total_estimated_cost'], 2);
                $tax = round($subtotal * 0.16, 2);
                $totalAmount = round($subtotal + $tax, 2);

                $po = PurchaseOrder::create([
                    'po_number' => $poCode,
                    'supplier_id' => $supplierId,
                    'status' => 'draft',
                    'total_units' => $group['total_deficit_units'],
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total_amount' => $totalAmount,
                    'currency' => 'MXN',
                    'estimated_delivery_at' => now()->addDays(7),
                    'notes' => "Orden generada automaticamente por auditoria de deficit BOM ({$procurementCode}) para lote de {$targetUnits} unidades de {$bundle->name}. Lote: {$batchRef}",
                ]);

                foreach ($group['items'] as $it) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $it['product_id'],
                        'product_variant_id' => $it['variant_id'],
                        'size_name' => $it['size'],
                        'quantity_ordered' => $it['deficit_qty'],
                        'quantity_received' => 0,
                        'unit_cost' => $it['unit_cost'],
                        'total_cost' => $it['estimated_deficit_cost'],
                    ]);

                    BomDeficitProcurement::where('bundle_id', $bundle->id)
                        ->where('component_product_id', $it['product_id'])
                        ->where('status', 'detected')
                        ->whereNull('purchase_order_id')
                        ->update([
                            'purchase_order_id' => $po->id,
                            'status' => 'po_drafted',
                        ]);
                }

                $createdPurchaseOrders[] = [
                    'id' => $po->po_number,
                    'dbId' => $po->id,
                    'poCode' => $po->po_number,
                    'supplierId' => $supplierId,
                    'supplierName' => $group['supplier']->name,
                    'totalPieces' => $po->total_units,
                    'subtotal' => (float) $po->subtotal,
                    'tax' => (float) $po->tax,
                    'totalAmount' => (float) $po->total_amount,
                    'status' => 'draft',
                    'statusLabel' => 'Borrador BOM',
                    'notes' => $po->notes,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => count($createdPurchaseOrders) > 0 
                    ? "Auditoria BOM completada. Se generaron " . count($createdPurchaseOrders) . " orden(es) de compra en borrador."
                    : "Auditoria BOM completada. El stock actual cubre al 100% la demanda del lote simulado.",
                'has_deficit' => $totalDeficitPieces > 0,
                'bundle_id' => $bundle->id,
                'bundle_sku' => $bundle->slug,
                'bundle_name' => $bundle->name,
                'target_units' => $targetUnits,
                'procurement_code' => $procurementCode,
                'batch_reference' => $batchRef,
                'total_required_pieces' => $totalRequiredPieces,
                'total_available_pieces' => $totalAvailablePieces,
                'total_deficit_pieces' => $totalDeficitPieces,
                'components' => $componentsAnalysis,
                'purchase_orders' => $createdPurchaseOrders,
            ], 200);
        });
    }
}

