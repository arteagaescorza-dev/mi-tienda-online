<?php

namespace Tests\Feature\RevOps;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\OperationalTask;
use App\Models\Order;
use App\Models\PackageType;
use App\Models\PackagingMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\RmaRequest;
use App\Models\RmaResolution;
use App\Models\Shipment;
use App\Models\ShippingDiscrepancyClaim;
use App\Models\ShippingReconciliation;
use App\Models\ShippingReconciliationItem;
use App\Models\ThreeWayMatching;
use App\Models\User;
use App\Models\VendorBill;
use App\Services\Finance\ShippingInvoiceAuditorService;
use App\Services\Finance\TaxCalculationEngineService;
use App\Services\Finance\ThreeWayMatchingService;
use App\Services\RevOps\PackagingFulfillmentService;
use App\Services\RevOps\RmaResolutionService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossHubSynergiesTest extends TestCase
{
    use RefreshDatabase;

    private function portalUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'director.crosshub@mitienda.test'],
            [
                'name' => 'Director de Operaciones CrossHub',
                'password' => bcrypt('password'),
                'role' => 'portal',
            ]
        );
    }

    private function createSampleVariant(string $sku, float $price = 399.00, float $cost = 120.00, int $initialStock = 50): array
    {
        $product = Product::create([
            'sku' => 'PROD-' . $sku,
            'title' => 'Prenda Intima Modelo ' . $sku,
            'slug' => 'prenda-intima-' . strtolower($sku),
            'brand' => 'Torzo',
            'visible' => true,
            'price' => $price,
            'cost' => $cost,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => $price,
            'cost' => $cost,
            'visible' => true,
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'cedis-norte',
            'quantity' => $initialStock,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        return [$product, $variant, $inventory];
    }

    /**
     * Flujo 1: Consumo automatico de insumos de empaque en expedicion y disparo de ROP a compras (TASK-INT-13).
     */
    public function test_flow_1_packaging_consumption_and_rop_procurement_trigger(): void
    {
        $box = PackageType::create([
            'code' => 'BOX-E2E-01',
            'name' => 'Caja Envio E2E',
            'type' => 'box',
            'length_cm' => 25,
            'width_cm' => 20,
            'height_cm' => 10,
            'max_weight_kg' => 3.0,
            'unit_cost' => 14.50,
            'stock_available' => 41,
            'reorder_point' => 40,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-SYN-001',
            'status' => 'processing',
            'total' => 850.00,
            'shipping_address' => ['city' => 'Monterrey', 'state' => 'NL', 'zip' => '64000', 'name' => 'Cliente CrossHub'],
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'EST-SYN-9988',
            'courier' => 'estafeta',
            'status' => 'label_created',
            'weight_actual_kg' => 0.85,
            'weight_volumetric_kg' => 1.00,
            'shipping_cost_declared' => 125.00,
        ]);

        $packagingService = new PackagingFulfillmentService();
        $user = $this->portalUser();

        // Empacar envio consumiendo la caja (41 - 1 = 40 <= 40 ROP)
        $movement = $packagingService->fulfillShipmentPackaging($shipment, $box, $user);

        $this->assertNotNull($movement);
        $this->assertEquals(40, $box->fresh()->stock_available);

        // Validar registro en Kardex de empaque
        $this->assertDatabaseHas('packaging_movements', [
            'package_type_id' => $box->id,
            'shipment_id' => $shipment->id,
            'type' => 'CONSUMO_EMPAQUE_ENVIO',
            'quantity' => -1,
            'stock_before' => 41,
            'stock_after' => 40,
        ]);

        // Validar disparo automatico de alerta ROP para compras
        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'compras',
            'title' => "Alerta ROP: Stock critico de caja {$box->code}",
            'priority' => 'urgente',
            'status' => 'pending',
        ]);
    }

    /**
     * Flujo 2: Protocolo sanitario de RMA intimo con precinto roto: baja como merma y cero stock comercial (TASK-INT-09).
     */
    public function test_flow_2_rma_sanitary_quarantine_prevents_biohazard_restock_and_compensates(): void
    {
        [$product, $variant, $inventory] = $this->createSampleVariant('SKU-RMA-E2E', 450.00, 150.00, 20);

        $order = Order::create([
            'order_number' => 'ORD-RMA-E2E-002',
            'status' => 'delivered',
            'total' => 450.00,
            'shipping_address' => ['city' => 'Guadalajara', 'state' => 'JAL', 'zip' => '44100'],
        ]);

        $rma = RmaRequest::create([
            'rma_number' => 'RMA-SYN-BIO-001',
            'order_id' => $order->id,
            'customer_name' => 'Cliente Afectado',
            'customer_phone' => '5511223344',
            'product_sku' => $variant->sku,
            'product_name' => 'Prenda Intima Modelo ' . $variant->sku,
            'reason' => 'sizing_wrong',
            'sanitary_status' => 'pending_inspection',
            'status' => 'open',
        ]);

        $service = new RmaResolutionService();

        // Inspeccion: precinto violado (has_hygiene_seal_intact = false)
        $result = $service->inspectAndResolve($rma, [
            'inspector_name' => 'Inspector Sanitario CrossHub',
            'has_hygiene_seal_intact' => false,
            'has_factory_tag' => false,
            'verdict' => 'approved', // Sera forzado a rejected por regla sanitaria PROFECO
            'resolution_type' => 'replacement_order',
            'technical_notes' => 'El cliente retiro el sello de seguridad plastico.',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('rejected_used_hygiene', $result['sanitary_status']);
        $this->assertEquals('rejected', $result['status']);

        // Invariante sanitaria: El stock vendible NUNCA debe incrementarse: permanece en 20
        $inventory->refresh();
        $this->assertEquals(20, $inventory->quantity);

        // Debe existir el asiento de baja como merma sanitaria
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 0,
            'reason' => 'SALIDA_MERMA_RMA - Destruccion por vulneracion de precinto sanitario PROFECO - Folio RMA-SYN-BIO-001',
        ]);
    }

    /**
     * Flujo 3: Auditoria volumetrica IATA de facturas courier y expediente de retencion CxP (TASK-INT-08).
     */
    public function test_flow_3_carrier_overweight_audit_and_cxp_hold_dossier(): void
    {
        $box = PackageType::create([
            'code' => 'BOX-AUDIT-E2E',
            'name' => 'Caja Mediana E2E',
            'type' => 'box',
            'length_cm' => 30,
            'width_cm' => 25,
            'height_cm' => 20, // Volumetrico IATA: (30*25*20)/5000 = 3.0 kg
            'max_weight_kg' => 5.0,
            'unit_cost' => 15.00,
            'stock_available' => 50,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-AUDIT-E2E-003',
            'status' => 'shipped',
            'total' => 1200.00,
            'shipping_address' => ['city' => 'Zapopan', 'state' => 'JAL'],
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'DHL-AUDIT-E2E-01',
            'courier' => 'dhl',
            'status' => 'delivered',
            'weight_actual_kg' => 1.50,
            'package_type_id' => $box->id,
        ]);

        // Simular archivo courier facturando 6.5 kg y cobrando $450
        $rawCsv = "guia,peso_kg,costo_facturado,pedido\nDHL-AUDIT-E2E-01,6.50,450.00,ORD-AUDIT-E2E-03";

        $service = app(ShippingInvoiceAuditorService::class);
        $recon = $service->auditCarrierInvoiceReport(
            carrierId: 'dhl',
            invoiceFolio: 'FAC-DHL-E2E-001',
            rawCsv: $rawCsv,
            auditedBy: 'Auditor CrossHub'
        );

        $this->assertInstanceOf(ShippingReconciliation::class, $recon);
        $this->assertEquals(1, $recon->discrepant_tracks_count);

        $item = $recon->items()->first();
        $this->assertEquals('discrepancy', $item->audit_status);
        $this->assertEquals(6.50, (float) $item->carrier_billed_weight_kg);
        $this->assertTrue($item->isOvercharged());

        // Levantar expediente formal de reclamacion
        $claim = $service->fileCarrierClaimDossier(
            reconciliation: $recon,
            selectedItemIds: [$item->id],
            claimNotes: 'Sobrepeso indebido en rampa vs cubicaje IATA',
            createdBy: 'Auditor CrossHub'
        );

        $this->assertInstanceOf(ShippingDiscrepancyClaim::class, $claim);
        $this->assertStringStartsWith('CLAIM-', $claim->claim_folio);
        $this->assertEquals(1, $claim->disputed_items_count);

        // Validar que se creo tarea de retencion preventiva para finanzas
        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'finance_discrepancy_hold',
            'title' => "Retencion de pago a paqueteria por {$claim->claim_folio}",
            'status' => 'pending',
        ]);
    }

    /**
     * Flujo 4: Cotejo de 3 vias Inbound bloquea SPEI ante discrepancias (TASK-INT-07).
     */
    public function test_flow_4_inbound_threeway_matching_and_spei_clearing(): void
    {
        $matchingService = app(ThreeWayMatchingService::class);

        // OC: 150 unidades a $200 = $30,000
        // Rampa: 140 recibidas, 10 danadas => 130 aceptables (Faltan 20 piezas = $4,000)
        // Factura proveedor: $30,000 por 150 piezas
        $matching = $matchingService->evaluateInboundMatch(
            poCode: 'OC-SYN-2026-004',
            supplierId: 'VND-TEX-01',
            supplierName: 'Textiles Mexicanos SA de CV',
            poQty: 150,
            poTotal: 30000.00,
            whReceivedQty: 140,
            whDamagedQty: 10,
            invoiceNumber: 'FAC-TEX-9901',
            invoiceQty: 150,
            invoiceGross: 30000.00,
            inspectedBy: 'Inspector Rampa CrossHub'
        );

        $this->assertEquals('discrepancy_held', $matching->match_status);
        $this->assertTrue($matching->spei_payout_blocked);
        $this->assertEquals(4000.00, (float) $matching->debit_note_amount);
        $this->assertEquals(26000.00, (float) $matching->payable_net_amount);

        $vendorBill = $matching->vendorBill;
        $this->assertNotNull($vendorBill);
        $this->assertTrue((bool) $vendorBill->is_spei_blocked);

        // Invariante de tesoreria: No se puede autorizar pago SPEI si hay discrepancia activa
        $this->expectException(DomainException::class);
        $matchingService->authorizeSpeiPayout($matching->id);
    }

    /**
     * Flujo 4 (Continuacion): Nota de Debito compensatoria solventa discrepancia y habilita SPEI (TASK-INT-07, TASK-FIN-02).
     */
    public function test_flow_4_part_b_debit_note_unblocks_spei_and_calculates_taxes(): void
    {
        $matchingService = app(ThreeWayMatchingService::class);
        $taxEngine = app(TaxCalculationEngineService::class);

        $matching = $matchingService->evaluateInboundMatch(
            poCode: 'OC-SYN-2026-005',
            supplierId: 'VND-CBA-02',
            supplierName: 'Confecciones del Bajio',
            poQty: 100,
            poTotal: 20000.00,
            whReceivedQty: 95,
            whDamagedQty: 5, // 90 aceptables, 10 faltantes/danadas ($2,000)
            invoiceNumber: 'FAC-CBA-8812',
            invoiceQty: 100,
            invoiceGross: 20000.00,
            inspectedBy: 'Inspector Rampa CrossHub'
        );

        $this->assertTrue($matching->spei_payout_blocked);

        // Emitir Nota de Debito compensatoria
        $updatedMatching = $matchingService->issueDebitNote(
            matchingId: $matching->id,
            debitNoteNumber: 'NC-CBA-2026-001'
        );

        $this->assertEquals('debit_note_issued', $updatedMatching->match_status);
        $this->assertFalse($updatedMatching->spei_payout_blocked);
        $this->assertEquals(18000.00, (float) $updatedMatching->payable_net_amount);

        // Ahora la dispersion SPEI es autorizable
        $clearedMatching = $matchingService->authorizeSpeiPayout(
            matchingId: $updatedMatching->id,
            paymentReference: 'SPEI-BANX-998822'
        );

        $this->assertEquals('approved_for_payment', $clearedMatching->match_status);

        $vendorBill = $clearedMatching->vendorBill;
        $this->assertEquals('paid', $vendorBill->status);
        $this->assertEquals('SPEI-BANX-998822', $vendorBill->payment_reference);

        // Desglose fiscal SAT
        $taxEntry = $taxEngine->computeOrderTaxes(1160.00, 'ORD-SYN-TAX-01');
        $this->assertEquals(1000.00, (float) $taxEntry->taxable_base);
        $this->assertEquals(160.00, (float) $taxEntry->iva_transferred_16);
        $this->assertEquals(10.00, (float) $taxEntry->platform_retention_isr_1);
        $this->assertEquals(80.00, (float) $taxEntry->platform_retention_iva_8);
        $this->assertEquals(80.00, (float) $taxEntry->net_iva_payable);
    }

    /**
     * Flujo 5: Orquestacion E2E Integrada de Sinergias Cross-Hub y Vistas Portal.
     */
    public function test_full_integrated_cross_hub_workflow_orchestration(): void
    {
        $user = $this->portalUser();
        $this->actingAs($user);

        // 1. Inbound de inventario y cotejo limpio
        $matchingService = app(ThreeWayMatchingService::class);
        $match = $matchingService->evaluateInboundMatch(
            poCode: 'OC-SYN-INTEGRATED',
            supplierId: 'VND-INTEGRAL',
            supplierName: 'Proveedor Integral SA',
            poQty: 50,
            poTotal: 10000.00,
            whReceivedQty: 50,
            whDamagedQty: 0,
            invoiceNumber: 'FAC-INT-100',
            invoiceQty: 50,
            invoiceGross: 10000.00,
            inspectedBy: 'Inspector Integral'
        );

        $this->assertEquals('matched', $match->match_status);
        $cleared = $matchingService->authorizeSpeiPayout($match->id, 'SPEI-DIRECT-OK');
        $this->assertEquals('approved_for_payment', $cleared->match_status);

        // 2. Expedicion y consumo de empaque
        $box = PackageType::create([
            'code' => 'BOX-INTEGRATED',
            'name' => 'Caja Integral',
            'type' => 'box',
            'length_cm' => 20,
            'width_cm' => 15,
            'height_cm' => 10,
            'max_weight_kg' => 2.0,
            'unit_cost' => 10.00,
            'stock_available' => 10,
            'reorder_point' => 5,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-INTEGRATED-01',
            'status' => 'processing',
            'total' => 600.00,
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'package_type_id' => $box->id,
            'tracking_number' => 'TRK-INT-55',
            'courier' => 'dhl',
            'status' => 'label_created',
            'weight_actual_kg' => 0.5,
            'weight_volumetric_kg' => 0.6,
            'shipping_cost_declared' => 145.00,
        ]);

        $packService = new PackagingFulfillmentService();
        $packService->fulfillShipmentPackaging($shipment, $box, $user);
        $this->assertEquals(9, $box->fresh()->stock_available);

        // 3. Auditoria de paqueteria sin discrepancia (tarifa contratada coincide exactamente)
        $auditorService = app(ShippingInvoiceAuditorService::class);
        $csvOk = "guia,peso_kg,costo_facturado,pedido\nTRK-INT-55,0.6,145.00,ORD-INTEGRATED-01\n";
        $audit = $auditorService->auditCarrierInvoiceReport('dhl', 'FACT-DHL-OK', $csvOk, 'Auditor Integral');
        $this->assertEquals(0, $audit->discrepant_tracks_count);

        // 4. Verificacion de portales web (Respuesta 200 en Logistica y Finanzas)
        $respLogistics = $this->get(route('portal.logistics'));
        $respLogistics->assertStatus(200);

        $respFinance = $this->get(route('portal.finance'));
        $respFinance->assertStatus(200);
    }
}
