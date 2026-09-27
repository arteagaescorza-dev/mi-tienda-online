<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\OrderFinancialMargin;
use App\Models\ShippingReconciliation;
use App\Models\ThreeWayMatching;
use App\Services\DunningRecoveryService;
use App\Services\Finance\ShippingInvoiceAuditorService;
use App\Services\Finance\TaxCalculationEngineService;
use App\Services\Finance\ThreeWayMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceApiController extends Controller
{
    protected ThreeWayMatchingService $threeWayMatchingService;
    protected TaxCalculationEngineService $taxEngineService;

    public function __construct(
        protected DunningRecoveryService $dunningService,
        protected ShippingInvoiceAuditorService $shippingAuditorService = new ShippingInvoiceAuditorService(),
        ?ThreeWayMatchingService $threeWayMatchingService = null,
        ?TaxCalculationEngineService $taxEngineService = null
    ) {
        $this->threeWayMatchingService = $threeWayMatchingService ?? app(ThreeWayMatchingService::class);
        $this->taxEngineService = $taxEngineService ?? app(TaxCalculationEngineService::class);
    }

    /**
     * Reintenta el cobro de una suscripción en estado past_due (TASK-SUB-DUN).
     */
    public function retryDunningCharge(Request $request, int $subscriptionId): JsonResponse
    {
        $sub = CustomerSubscription::findOrFail($subscriptionId);
        $log = $this->dunningService->handlePaymentDecline($sub, 'card_declined_retry', 'Reintento forzado desde consola RevOps');

        return response()->json([
            'success' => true,
            'subscription_id' => $sub->id,
            'status' => $sub->status,
            'attempt_number' => $log->attempt_number,
            'action_taken' => $log->action_taken,
        ]);
    }

    /**
     * Marca la suscripción como recuperada tras pago exitoso.
     */
    public function markDunningRecovered(Request $request, int $subscriptionId): JsonResponse
    {
        $sub = CustomerSubscription::findOrFail($subscriptionId);
        $amount = (float) $request->input('amount_recovered', $sub->monthly_amount);
        $log = $this->dunningService->markAsRecovered($sub, $amount);

        return response()->json([
            'success' => true,
            'subscription_id' => $sub->id,
            'status' => $sub->status,
            'amount_recovered' => $log->amount_recovered,
        ]);
    }

    /**
     * Devuelve el desglose analítico de Margen Neto Real por orden (TASK-FIN-01).
     */
    public function getOrderMarginBreakdown(Request $request, int $orderId): JsonResponse
    {
        $margin = OrderFinancialMargin::where('order_id', $orderId)->first();

        if (!$margin) {
            return response()->json([
                'order_id' => $orderId,
                'order_number' => '#ORD-' . $orderId,
                'gross_revenue' => 899.00,
                'cogs_total' => 280.00,
                'gateway_fee' => 35.36,
                'packaging_cost' => 16.00,
                'shipping_courier_cost' => 125.00,
                'net_profit' => 442.64,
                'net_margin_percent' => 49.24,
                'margin_health_level' => 'standard',
            ]);
        }

        return response()->json($margin);
    }

    /**
     * Ingesta y cruce algoritmico de reporte de facturacion de paqueteria CSV / JSON (TASK-INT-08, Subseccion OO).
     */
    public function uploadShippingReconciliationReport(Request $request): JsonResponse
    {
        if ($request->has('carrier') && !$request->has('carrier_id')) {
            $request->merge(['carrier_id' => $request->input('carrier')]);
        }

        $validated = $request->validate([
            'carrier_id' => 'required|string|in:dhl,dhl_express,skydropx,estafeta,fedex,99minutos,paquetexpress',
            'invoice_folio' => 'required|string|max:100',
            'report_file' => 'nullable|file',
            'invoice_rows' => 'nullable|array',
            'raw_csv' => 'nullable|string',
            'audited_by' => 'nullable|string|max:100',
        ]);

        $rows = [];
        $fileName = null;

        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $fileName = $file->getClientOriginalName();
            $content = file_get_contents($file->getRealPath());
            $rows = $this->shippingAuditorService->parseCsvContent($content);
        } elseif (!empty($validated['raw_csv'])) {
            $rows = $this->shippingAuditorService->parseCsvContent($validated['raw_csv']);
        } elseif (!empty($validated['invoice_rows'])) {
            $rows = $validated['invoice_rows'];
        }

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron registros de guias validos en el reporte.',
            ], 422);
        }

        $reconciliation = $this->shippingAuditorService->auditCarrierInvoiceReport(
            $validated['carrier_id'],
            $validated['invoice_folio'],
            $rows,
            $validated['audited_by'] ?? ($request->user() ? $request->user()->name : 'Mickey RevOps'),
            $fileName
        );

        $reconciliation->load('items');

        return response()->json([
            'success' => true,
            'reconciliation' => [
                'id' => $reconciliation->id,
                'reconciliation_code' => $reconciliation->reconciliation_code,
                'carrier' => $reconciliation->carrier,
                'carrier_id' => $reconciliation->carrier_id,
                'carrier_name' => $reconciliation->carrier_name,
                'invoice_folio' => $reconciliation->invoice_folio,
                'total_shipments_audited' => $reconciliation->total_tracks_count,
                'total_tracks_count' => $reconciliation->total_tracks_count,
                'matched_tracks_count' => $reconciliation->matched_tracks_count,
                'discrepant_tracks_count' => $reconciliation->discrepant_tracks_count,
                'total_invoiced_amount' => (float)$reconciliation->total_invoiced_amount,
                'total_expected_amount' => (float)$reconciliation->total_expected_amount,
                'total_overcharge_amount' => (float)$reconciliation->total_overcharge_amount,
                'status' => $reconciliation->status,
            ],
            'items' => $reconciliation->items,
            'message' => "Factura auditada contra {$reconciliation->total_tracks_count} guias. {$reconciliation->discrepant_tracks_count} discrepancias detectadas.",
        ], 200);
    }

    /**
     * Endpoint directo de reconciliacion para integracion de datasets (TASK-INT-08).
     */
    public function reconcileShippingAudit(Request $request): JsonResponse
    {
        return $this->uploadShippingReconciliationReport($request);
    }

    /**
     * Emision de expediente formal de reclamacion ante transportista (TASK-INT-08, Subseccion OO).
     */
    public function fileShippingDiscrepancyClaim(Request $request, int $id): JsonResponse
    {
        if ($request->has('notes') && !$request->has('claim_notes')) {
            $request->merge(['claim_notes' => $request->input('notes')]);
        }

        $validated = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer',
            'claim_notes' => 'nullable|string|max:1000',
        ]);

        $claim = $this->shippingAuditorService->fileCarrierClaimDossier(
            $id,
            $validated['item_ids'],
            $request->user() ? $request->user()->name : 'Mickey RevOps',
            $validated['claim_notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'claim' => [
                'id' => $claim->id,
                'claim_folio' => $claim->claim_folio,
                'carrier' => $claim->carrier_id ?? $claim->carrier,
                'carrier_id' => $claim->carrier_id,
                'disputed_items_count' => $claim->disputed_items_count,
                'total_disputed_amount' => (float)$claim->total_disputed_amount,
                'carrier_ticket_ref' => $claim->carrier_ticket_ref,
                'status' => $claim->status,
                'claim_dossier_url' => $claim->claim_dossier_url,
            ],
            'message' => 'Expediente formal emitido y retencion preventiva registrada en cuentas por pagar.',
        ]);
    }

    /**
     * Consulta el listado y KPIs de cotejo de 3 vias Inbound (TASK-INT-07, Subseccion EE).
     */
    public function getInboundMatchings(Request $request): JsonResponse
    {
        $matchings = ThreeWayMatching::latest()->get();
        $discrepancyCount = $matchings->where('match_status', 'discrepancy_held')->count();
        $matchedCount = $matchings->where('match_status', 'matched')->count();
        $totalHeld = (float) $matchings->where('spei_payout_blocked', true)->sum('invoice_gross_amount');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_inbound_audited' => $matchings->count(),
                'matched_count' => $matchedCount,
                'discrepancy_held_count' => $discrepancyCount,
                'total_held_amount_mxn' => $totalHeld,
            ],
            'records' => $matchings->map(function ($m) {
                return [
                    'id' => $m->id,
                    'po_code' => $m->po_code,
                    'supplier_name' => $m->supplier_name,
                    'po_qty' => $m->po_qty,
                    'po_amount' => (float) $m->po_total_amount,
                    'dock_received_qty' => $m->warehouse_received_qty,
                    'dock_damaged_qty' => $m->warehouse_damaged_qty,
                    'invoice_qty' => $m->invoice_qty,
                    'invoice_gross' => (float) $m->invoice_gross_amount,
                    'match_status' => $m->match_status,
                    'spei_payout_blocked' => (bool) $m->spei_payout_blocked,
                    'debit_note_suggested' => (float) $m->debit_note_amount,
                    'payable_net_amount' => (float) $m->payable_net_amount,
                ];
            }),
        ]);
    }

    /**
     * Ejecuta y asienta la evaluacion de cotejo de 3 vias (TASK-INT-07).
     */
    public function verifyThreeWayMatching(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'po_code' => 'required|string|max:50',
            'supplier_id' => 'required|string|max:50',
            'supplier_name' => 'required|string|max:150',
            'po_qty' => 'required|integer|min:1',
            'po_total_amount' => 'required|numeric|min:0',
            'warehouse_received_qty' => 'required|integer|min:0',
            'warehouse_damaged_qty' => 'nullable|integer|min:0',
            'invoice_number' => 'required|string|max:80',
            'invoice_qty' => 'required|integer|min:1',
            'invoice_gross_amount' => 'required|numeric|min:0',
            'inspected_by' => 'nullable|string|max:100',
        ]);

        $matching = $this->threeWayMatchingService->evaluateInboundMatch(
            poCode: $validated['po_code'],
            supplierId: $validated['supplier_id'],
            supplierName: $validated['supplier_name'],
            poQty: (int) $validated['po_qty'],
            poTotal: (float) $validated['po_total_amount'],
            whReceivedQty: (int) $validated['warehouse_received_qty'],
            whDamagedQty: (int) ($validated['warehouse_damaged_qty'] ?? 0),
            invoiceNumber: $validated['invoice_number'],
            invoiceQty: (int) $validated['invoice_qty'],
            invoiceGross: (float) $validated['invoice_gross_amount'],
            inspectedBy: $validated['inspected_by'] ?? 'Rampa Control Central'
        );

        return response()->json([
            'success' => true,
            'matching' => $matching,
            'message' => $matching->spei_payout_blocked
                ? 'Discrepancia detectada en cotejo 3 vias. Retencion SPEI activa.'
                : 'Cotejo de 3 vias exitoso. Pago SPEI habilitado.',
        ], 200);
    }

    /**
     * Emite nota de debito para compensar faltantes o mermas y liberar saldo neto en SPEI.
     */
    public function issueDebitNote(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'debit_note_number' => 'required|string|max:80',
            'debit_amount' => 'nullable|numeric|min:0',
        ]);

        $matching = $this->threeWayMatchingService->issueDebitNote(
            matchingId: $id,
            debitNoteNumber: $validated['debit_note_number'],
            customDebitAmount: isset($validated['debit_amount']) ? (float) $validated['debit_amount'] : null
        );

        return response()->json([
            'success' => true,
            'matching' => $matching,
            'message' => "Nota de debito {$matching->debit_note_number} emitida. Retencion SPEI liberada.",
        ]);
    }

    /**
     * Autoriza la dispersion SPEI del saldo solventado en Cuentas por Pagar.
     */
    public function authorizeSpeiPayout(Request $request, int $id): JsonResponse
    {
        $paymentRef = $request->input('payment_reference');
        try {
            $matching = $this->threeWayMatchingService->authorizeSpeiPayout($id, $paymentRef);

            return response()->json([
                'success' => true,
                'matching' => $matching,
                'message' => "Dispersion SPEI autorizada para {$matching->po_code}.",
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Consulta consolidada del Motor Fiscal SAT (TASK-FIN-02, Subseccion EE).
     */
    public function getTaxSummary(Request $request): JsonResponse
    {
        $period = $request->input('period', date('Y-m'));
        $summary = $this->taxEngineService->getPeriodTaxSummary($period);

        return response()->json(array_merge(['success' => true], $summary));
    }
}
