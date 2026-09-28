<?php

namespace Tests\Feature\Integration;

use App\Models\OperationalTask;
use App\Models\Product;
use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierCreditAgreement;
use App\Models\User;
use App\Models\VendorShipment;
use App\Models\WarehouseRampEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementExtranetRampE2ETest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba E2E completa del ciclo de adquisiciones:
     * Discrepancia Previa -> Emision OC con Deduccion (Hub 4) -> Aceptacion en Extranet (Hub 7)
     * -> Despacho Fletero B2B -> Sincronizacion Rampa (Hub 4) & Tareas (Hub 1) -> Impresion Oficial.
     */
    public function test_complete_procurement_extranet_ramp_lifecycle(): void
    {
        // -------------------------------------------------------------
        // FASE 0: CONFIGURACION DE ACTORES Y CATALOGO BASE
        // -------------------------------------------------------------
        $supplier = Supplier::create([
            'name' => 'Confecciones Textiles del Bajio SA de CV',
            'contact_name' => 'Roberto Almanza',
            'email' => 'roberto@textilesbajio.com',
            'phone' => '4771234567',
            'is_active' => true,
        ]);

        $intruderSupplier = Supplier::create([
            'name' => 'Taller Textil Independiente',
            'contact_name' => 'Sara Gomez',
            'email' => 'sara@independiente.com',
            'phone' => '4779876543',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $supplier->id,
        ]);
        $intruderUser = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $intruderSupplier->id,
        ]);

        $product = Product::create([
            'title' => 'Boxer Boxer Brief Titan',
            'slug' => 'boxer-brief-titan',
            'sku' => 'BOX-TITAN-01',
            'price' => 450.00,
            'stock' => 100,
        ]);

        // -------------------------------------------------------------
        // FASE 1: REGISTRO DE MINUTA DE DISCREPANCIA INBOUND PREVIA
        // -------------------------------------------------------------
        $this->actingAs($admin);

        $minutePayload = [
            'supplier_id' => $supplier->id,
            'discrepancy_type' => 'missing_items',
            'missing_units_count' => 10,
            'damaged_units_count' => 0,
            'unit_cost_applied' => 120.00,
            'authorized_by_vendor_contact' => 'Roberto Almanza',
            'resolution_notes' => 'Faltante de 10 piezas en recepcion rampa lote anterior',
        ];

        $respMinute = $this->postJson('/api/v1/inventory/purchases/credit-agreements', $minutePayload);
        $respMinute->assertStatus(201);
        $respMinute->assertJson(['success' => true]);

        $agreementId = $respMinute->json('agreement.id');
        $agreementFolio = $respMinute->json('agreement.ref');

        $this->assertDatabaseHas('supplier_credit_agreements', [
            'id' => $agreementId,
            'status' => 'pending',
            'amount' => 1200.00,
        ]);

        $this->assertDatabaseHas('purchase_kardex', [
            'supplier_id' => $supplier->id,
            'movement_type' => 'DISCREPANCY_REGISTERED',
        ]);

        // -------------------------------------------------------------
        // FASE 2: EMISION DE OC CON COMPENSACION MERCANTIL EN HUB 4
        // -------------------------------------------------------------
        $poPayload = [
            'supplier_id' => $supplier->id,
            'has_credit_deduction' => true,
            'credit_agreement_id' => $agreementId,
            'priority' => 'critica',
            'expected_delivery_date' => now()->addDays(10)->format('Y-m-d'),
            'items' => [
                [
                    'sku' => 'BOX-TITAN-01-M',
                    'product_id' => $product->id,
                    'qty' => 100,
                    'unit_cost' => 120.00,
                ],
            ],
            'notes' => 'Pedido con deduccion acordada bajo minuta previa.',
        ];

        $respPo = $this->postJson('/api/v1/inventory/purchases/orders', $poPayload);
        $respPo->assertStatus(201);
        $respPo->assertJson(['success' => true]);

        $poId = $respPo->json('order.dbId');
        $poNumber = $respPo->json('order.poNumber') ?: $respPo->json('order.id');

        // Validar calculo fiscal: Subtotal bruto 12,000 - Credito 1,200 = Base 10,800 + IVA 16% 1,728 = Total 12,528
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $poId,
            'supplier_id' => $supplier->id,
            'gross_subtotal' => 12000.00,
            'credit_deduction_amount' => 1200.00,
            'subtotal' => 10800.00,
            'tax' => 1728.00,
            'total_amount' => 12528.00,
            'status' => 'confirmed',
            'priority' => 'critica',
        ]);

        // El acuerdo pasa a applied y se vincula a la OC
        $this->assertDatabaseHas('supplier_credit_agreements', [
            'id' => $agreementId,
            'status' => 'applied',
            'applied_to_po_id' => $poId,
        ]);

        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $poId,
            'movement_type' => 'CREDIT_NOTE_APPLIED',
        ]);

        // -------------------------------------------------------------
        // FASE 3: CONSULTA Y ACEPTACION EN EXTRANET HUB 7 (ROL VENDOR)
        // -------------------------------------------------------------
        $this->actingAs($vendorUser);

        // 3.1. Consulta de OCs en Extranet
        $respVendorOrders = $this->getJson('/api/v1/hub7/vendor/orders');
        $respVendorOrders->assertStatus(200);
        $respVendorOrders->assertJson([
            'success' => true,
            'supplier_id' => $supplier->id,
            'orders_count' => 1,
        ]);

        // 3.2. Consulta de detalle de la OC
        $respDetail = $this->getJson("/api/v1/hub7/vendor/orders/{$poId}");
        $respDetail->assertStatus(200);
        $respDetail->assertJson([
            'success' => true,
            'order' => [
                'id' => $poId,
                'po_number' => $poNumber,
                'status' => 'confirmed',
            ],
        ]);

        // 3.3. Confirmacion de recepcion y programacion de corte
        $respConfirm = $this->postJson("/api/v1/hub7/vendor/orders/{$poId}/confirm");
        $respConfirm->assertStatus(200);
        $respConfirm->assertJson(['success' => true]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $poId,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $poId,
            'movement_type' => 'PO_CONFIRMED_BY_VENDOR',
        ]);

        // -------------------------------------------------------------
        // FASE 4: DESPACHO FLETERO B2B Y SINCRONIZACION CON RAMPA
        // -------------------------------------------------------------
        $dispatchPayload = [
            'carrier_name' => 'Transportes Castores',
            'tracking_number' => 'CAS-BAJIO-2026-99',
            'remission_number' => 'REM-TB-0042',
            'packages_count' => 5,
            'dispatched_units' => 100,
            'driver_name' => 'Guillermo Parra',
            'license_plate' => '22-TR-9A',
            'estimated_arrival_date' => now()->addDays(2)->format('Y-m-d'),
            'notes' => 'Cinco bultos debidamente rotulados por modelo y talla.',
        ];

        $respDispatch = $this->postJson("/api/v1/hub7/vendor/orders/{$poId}/dispatch", $dispatchPayload);
        $respDispatch->assertStatus(200);
        $respDispatch->assertJson(['success' => true]);

        // Validar actualizacion a in_transit
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $poId,
            'status' => 'in_transit',
            'carrier' => 'Transportes Castores',
            'tracking_number' => 'CAS-BAJIO-2026-99',
        ]);

        // Validar registro en vendor_shipments
        $this->assertDatabaseHas('vendor_shipments', [
            'po_code' => $poNumber,
            'carrier_name' => 'Transportes Castores',
            'tracking_number' => 'CAS-BAJIO-2026-99',
            'remission_number' => 'REM-TB-0042',
            'pieces_dispatched' => 100,
            'status' => 'in_transit',
        ]);

        // Validar asiento en Kardex
        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $poId,
            'movement_type' => 'PO_DISPATCHED_BY_VENDOR',
        ]);

        // Validar entrada en rampa de bodega (Hub 4)
        $this->assertDatabaseHas('warehouse_ramp_entries', [
            'packages_received_count' => 5,
            'driver_name' => 'Guillermo Parra',
            'license_plate' => '22-TR-9A',
            'ramp_number' => 'RAMPA_01',
            'status' => 'in_dock',
        ]);

        // Validar generacion de tarea operativa en Hub 1 para almacen
        $this->assertDatabaseHas('operational_tasks', [
            'reference_code' => $poNumber,
            'assigned_role' => 'warehouse',
            'category' => 'almacen',
            'priority' => 'critical',
            'status' => 'pending',
        ]);

        // -------------------------------------------------------------
        // FASE 5: DOCUMENTO OFICIAL B2B CON CLAUSULA 4
        // -------------------------------------------------------------
        $respPrint = $this->get("/admin/extranet/proveedores/ordenes/{$poId}/imprimir");
        $respPrint->assertStatus(200);
        $respPrint->assertSee($poNumber);
        $respPrint->assertSee('Confecciones Textiles del Bajio');
        $respPrint->assertSee('Clausula 4: Compensacion Mercantil');
        $respPrint->assertSee(number_format(1200, 2));

        // -------------------------------------------------------------
        // FASE 6: AISLAMIENTO MULTI-INQUILINO ESTRICTO
        // -------------------------------------------------------------
        $this->actingAs($intruderUser);

        // Intruso no ve la orden en su lista
        $respIntruderOrders = $this->getJson('/api/v1/hub7/vendor/orders');
        $respIntruderOrders->assertStatus(200);
        $respIntruderOrders->assertJson(['orders_count' => 0]);

        // Intruso no puede ver detalle (403)
        $this->getJson("/api/v1/hub7/vendor/orders/{$poId}")->assertStatus(403);

        // Intruso no puede confirmar (403)
        $this->postJson("/api/v1/hub7/vendor/orders/{$poId}/confirm")->assertStatus(403);

        // Intruso no puede despachar (403)
        $this->postJson("/api/v1/hub7/vendor/orders/{$poId}/dispatch", $dispatchPayload)->assertStatus(403);

        // Intruso no puede imprimir (403)
        $this->get("/admin/extranet/proveedores/ordenes/{$poId}/imprimir")->assertStatus(403);
    }
}
