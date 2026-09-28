<?php

namespace Tests\Feature\Inventory;

use App\Models\OperationalTask;
use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\VendorShipment;
use App\Models\WarehouseRampEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorShipmentRampSyncTest extends TestCase
{
    use RefreshDatabase;

    protected Supplier $supplierA;
    protected Supplier $supplierB;
    protected User $vendorUserA;
    protected User $vendorUserB;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierA = Supplier::create([
            'name' => 'Textiles El Triunfo SA de CV',
            'contact_name' => 'Rodrigo Santos',
            'email' => 'rodrigo@eltriunfo.com',
            'phone' => '5544332211',
            'is_active' => true,
        ]);

        $this->supplierB = Supplier::create([
            'name' => 'Fabrica Confecciones del Sur',
            'contact_name' => 'Lorena Mendez',
            'email' => 'lorena@confeccionessur.com',
            'phone' => '5577889900',
            'is_active' => true,
        ]);

        $this->vendorUserA = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $this->supplierA->id,
        ]);

        $this->vendorUserB = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $this->supplierB->id,
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Verifica que un fabricante registre el despacho de su OC, sincronizando status, rampa y tarea operativa (TASK-INT-20).
     */
    public function test_vendor_can_dispatch_order_creating_shipment_ramp_entry_and_task(): void
    {
        $orderA = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'po_number' => 'PO-DISPATCH-01',
            'status' => 'confirmed',
            'priority' => 'urgent',
            'total_units' => 150,
            'subtotal' => 22500.00,
            'tax_amount' => 3600.00,
            'total_amount' => 26100.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $payload = [
            'carrier_name' => 'Transportes Castores',
            'tracking_number' => 'CAS-998877-MX',
            'remission_number' => 'REM-2026-TRIUNFO-01',
            'packages_count' => 4,
            'dispatched_units' => 150,
            'driver_name' => 'Juan Hernandez',
            'license_plate' => '88-ABC-9',
            'estimated_arrival_date' => now()->addDays(2)->format('Y-m-d'),
            'notes' => 'Mercancia paletizada con emplaye de seguridad.',
        ];

        $response = $this->postJson("/api/v1/hub7/vendor/orders/{$orderA->id}/dispatch", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // 1. Validar que la orden paso a in_transit con datos de transportista
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $orderA->id,
            'status' => 'in_transit',
            'carrier' => 'Transportes Castores',
            'tracking_number' => 'CAS-998877-MX',
        ]);

        // 2. Validar registro en vendor_shipments
        $this->assertDatabaseHas('vendor_shipments', [
            'po_code' => 'PO-DISPATCH-01',
            'supplier_id' => (string) $this->supplierA->id,
            'carrier_name' => 'Transportes Castores',
            'tracking_number' => 'CAS-998877-MX',
            'remission_number' => 'REM-2026-TRIUNFO-01',
            'pieces_dispatched' => 150,
            'status' => 'in_transit',
        ]);

        // 3. Validar asiento inmutable en purchase_kardex
        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $orderA->id,
            'supplier_id' => $this->supplierA->id,
            'movement_type' => 'PO_DISPATCHED_BY_VENDOR',
        ]);

        // 4. Validar sincronizacion con rampa de recepcion en Hub 4
        $this->assertDatabaseHas('warehouse_ramp_entries', [
            'packages_received_count' => 4,
            'driver_name' => 'Juan Hernandez',
            'license_plate' => '88-ABC-9',
            'ramp_number' => 'RAMPA_01',
            'status' => 'in_dock',
        ]);

        // 5. Validar generacion de tarea operativa en Hub 1 para el almacen
        $this->assertDatabaseHas('operational_tasks', [
            'reference_code' => 'PO-DISPATCH-01',
            'assigned_role' => 'warehouse',
            'category' => 'almacen',
            'priority' => 'critical',
            'status' => 'pending',
        ]);
    }

    /**
     * Verifica que un proveedor no pueda registrar despacho en una orden de otro taller (aislamiento B2B).
     */
    public function test_vendor_cannot_dispatch_order_of_another_supplier(): void
    {
        $orderB = PurchaseOrder::create([
            'supplier_id' => $this->supplierB->id,
            'po_number' => 'PO-CONFIDENTIAL-B2',
            'status' => 'confirmed',
            'priority' => 'normal',
            'total_units' => 60,
            'subtotal' => 9000.00,
            'tax_amount' => 1440.00,
            'total_amount' => 10440.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $payload = [
            'carrier_name' => 'Fletera Intruso',
            'tracking_number' => 'TRACK-ILLEGAL-99',
            'packages_count' => 1,
        ];

        $response = $this->postJson("/api/v1/hub7/vendor/orders/{$orderB->id}/dispatch", $payload);

        $response->assertStatus(403);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $orderB->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseMissing('vendor_shipments', [
            'po_code' => 'PO-CONFIDENTIAL-B2',
        ]);
    }

    /**
     * Verifica que no se pueda registrar despacho sobre una orden cancelada.
     */
    public function test_vendor_cannot_dispatch_cancelled_order(): void
    {
        $cancelledOrder = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'po_number' => 'PO-CANCELLED-01',
            'status' => 'cancelled',
            'priority' => 'normal',
            'total_units' => 50,
            'subtotal' => 7500.00,
            'tax_amount' => 1200.00,
            'total_amount' => 8700.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $payload = [
            'carrier_name' => 'Transportes Tres Guerras',
            'tracking_number' => 'TG-443322',
        ];

        $response = $this->postJson("/api/v1/hub7/vendor/orders/{$cancelledOrder->id}/dispatch", $payload);
        $response->assertStatus(422);
    }

    /**
     * Verifica que el proveedor pueda consultar el expediente de remision/guia de su orden despachada.
     */
    public function test_vendor_can_query_shipment_details_of_dispatched_order(): void
    {
        $orderA = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'po_number' => 'PO-QUERY-SHIP-01',
            'status' => 'confirmed',
            'priority' => 'normal',
            'total_units' => 80,
            'subtotal' => 12000.00,
            'tax_amount' => 1920.00,
            'total_amount' => 13920.00,
        ]);

        VendorShipment::create([
            'po_id' => (string) $orderA->id,
            'po_code' => $orderA->po_number,
            'supplier_id' => (string) $this->supplierA->id,
            'carrier_name' => 'Paquetexpress Carga',
            'tracking_number' => 'PAQ-778899-CDMX',
            'pieces_dispatched' => 80,
            'remission_number' => 'REM-TRIUNFO-99',
            'status' => 'in_transit',
            'dispatched_at' => now(),
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->getJson("/api/v1/hub7/vendor/orders/{$orderA->id}/shipment");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'order_id' => $orderA->id,
            'po_number' => 'PO-QUERY-SHIP-01',
            'shipment' => [
                'carrier_name' => 'Paquetexpress Carga',
                'tracking_number' => 'PAQ-778899-CDMX',
                'pieces_dispatched' => 80,
            ],
        ]);
    }

    /**
     * Verifica que un proveedor no pueda consultar el despacho de una orden ajena.
     */
    public function test_vendor_cannot_query_shipment_of_another_supplier(): void
    {
        $orderB = PurchaseOrder::create([
            'supplier_id' => $this->supplierB->id,
            'po_number' => 'PO-PRIVATE-SHIP-B',
            'status' => 'in_transit',
            'priority' => 'normal',
            'total_units' => 40,
            'subtotal' => 6000.00,
            'tax_amount' => 960.00,
            'total_amount' => 6960.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->getJson("/api/v1/hub7/vendor/orders/{$orderB->id}/shipment");
        $response->assertStatus(403);
    }

    /**
     * Verifica que un administrador global tenga autorizacion para registrar despacho de cualquier proveedor.
     */
    public function test_global_admin_can_dispatch_on_behalf_of_vendor(): void
    {
        $orderB = PurchaseOrder::create([
            'supplier_id' => $this->supplierB->id,
            'po_number' => 'PO-ADMIN-DISPATCH',
            'status' => 'confirmed',
            'priority' => 'normal',
            'total_units' => 70,
            'subtotal' => 10500.00,
            'tax_amount' => 1680.00,
            'total_amount' => 12180.00,
        ]);

        $this->actingAs($this->adminUser);

        $payload = [
            'carrier_name' => 'Transportes Tres Guerras',
            'tracking_number' => 'TG-ADMIN-8822',
            'remission_number' => 'REM-ADMIN-01',
            'packages_count' => 2,
        ];

        $response = $this->postJson("/api/v1/hub7/vendor/orders/{$orderB->id}/dispatch", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $orderB->id,
            'status' => 'in_transit',
            'carrier' => 'Transportes Tres Guerras',
        ]);
    }
}
