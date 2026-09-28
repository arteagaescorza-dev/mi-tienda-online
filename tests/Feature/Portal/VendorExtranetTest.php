<?php

namespace Tests\Feature\Portal;

use App\Models\Ally;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierCreditAgreement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorExtranetTest extends TestCase
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
            'name' => 'Confecciones del Valle SA de CV',
            'contact_name' => 'Mariana Morales',
            'email' => 'mariana@confeccionesvalle.com',
            'phone' => '5522334455',
            'is_active' => true,
        ]);

        $this->supplierB = Supplier::create([
            'name' => 'Textilera Azteca SA de CV',
            'contact_name' => 'Pedro Solis',
            'email' => 'pedro@textileraazteca.com',
            'phone' => '5566778899',
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
     * Verifica que un usuario con rol vendor acceda a la vista principal de la extranet en Hub 7.
     */
    public function test_vendor_user_can_access_vendor_extranet_view(): void
    {
        $this->actingAs($this->vendorUserA);

        $response = $this->get('/admin/extranet/proveedores');

        $response->assertStatus(200);
        $response->assertSee('Hub 7');
        $response->assertSee('Portal de Proveedores');
        $response->assertSee($this->supplierA->name);
    }

    /**
     * Verifica que un usuario vendor huerfano (sin supplier_id) sea bloqueado con HTTP 403.
     */
    public function test_vendor_without_supplier_id_is_forbidden(): void
    {
        $orphanVendor = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => null,
        ]);

        $this->actingAs($orphanVendor);

        $response = $this->get('/admin/extranet/proveedores');
        $response->assertStatus(403);
    }

    /**
     * Verifica que un usuario con rol ally sea bloqueado de la extranet de proveedores con HTTP 403.
     */
    public function test_ally_user_is_forbidden_from_vendor_extranet(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-FORB-VND',
            'name' => 'Club Aliado',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'portal_token' => 'tok_forb_vnd_01',
        ]);

        $allyUser = User::factory()->create([
            'role' => 'ally',
            'ally_id' => $ally->id,
        ]);

        $this->actingAs($allyUser);

        $response = $this->get('/admin/extranet/proveedores');
        $response->assertStatus(403);
    }

    /**
     * Verifica que un administrador global pueda acceder a la extranet y emular a cualquier proveedor.
     */
    public function test_global_admin_can_access_vendor_extranet_and_simulate_supplier(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get("/admin/extranet/proveedores?supplier_id={$this->supplierB->id}");

        $response->assertStatus(200);
        $response->assertSee($this->supplierB->name);
        $response->assertSee('Modo Auditoria');
    }

    /**
     * Verifica que la API de ordenes retorne exclusivamente las OCs del proveedor autenticado (aislamiento B2B).
     */
    public function test_vendor_api_get_orders_returns_only_orders_of_current_supplier(): void
    {
        // Crear 2 ordenes para Supplier A
        PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'po_number' => 'PO-TEST-A1',
            'status' => 'sent',
            'priority' => 'normal',
            'total_units' => 100,
            'subtotal' => 15000.00,
            'tax_amount' => 2400.00,
            'total_amount' => 17400.00,
        ]);

        PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'po_number' => 'PO-TEST-A2',
            'status' => 'confirmed',
            'priority' => 'urgent',
            'total_units' => 50,
            'subtotal' => 8000.00,
            'tax_amount' => 1280.00,
            'total_amount' => 9280.00,
        ]);

        // Crear 1 orden para Supplier B
        PurchaseOrder::create([
            'supplier_id' => $this->supplierB->id,
            'po_number' => 'PO-TEST-B1',
            'status' => 'sent',
            'priority' => 'critical',
            'total_units' => 200,
            'subtotal' => 30000.00,
            'tax_amount' => 4800.00,
            'total_amount' => 34800.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->getJson('/api/v1/hub7/vendor/orders');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'supplier_id' => $this->supplierA->id,
            'orders_count' => 2,
        ]);

        $orders = $response->json('orders');
        $poNumbers = collect($orders)->pluck('po_number')->all();

        $this->assertContains('PO-TEST-A1', $poNumbers);
        $this->assertContains('PO-TEST-A2', $poNumbers);
        $this->assertNotContains('PO-TEST-B1', $poNumbers);
    }

    /**
     * Verifica que un proveedor no pueda consultar el detalle de una orden de otro proveedor.
     */
    public function test_vendor_cannot_view_order_detail_of_another_supplier(): void
    {
        $orderB = PurchaseOrder::create([
            'supplier_id' => $this->supplierB->id,
            'po_number' => 'PO-CONFIDENTIAL-B',
            'status' => 'sent',
            'priority' => 'normal',
            'total_units' => 120,
            'subtotal' => 20000.00,
            'tax_amount' => 3200.00,
            'total_amount' => 23200.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->getJson("/api/v1/hub7/vendor/orders/{$orderB->id}");
        $response->assertStatus(403);
    }

    /**
     * Verifica que un proveedor pueda confirmar la recepcion de su OC y se genere el asiento en Kardex.
     */
    public function test_vendor_can_confirm_order_reception_and_logs_kardex(): void
    {
        $orderA = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'po_number' => 'PO-ACCEPT-01',
            'status' => 'sent',
            'priority' => 'normal',
            'total_units' => 80,
            'subtotal' => 12000.00,
            'tax_amount' => 1920.00,
            'total_amount' => 13920.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->postJson("/api/v1/hub7/vendor/orders/{$orderA->id}/confirm");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $orderA->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $orderA->id,
            'supplier_id' => $this->supplierA->id,
            'movement_type' => 'PO_CONFIRMED_BY_VENDOR',
        ]);
    }

    /**
     * Verifica que un proveedor no pueda confirmar una orden perteneciente a otro taller.
     */
    public function test_vendor_cannot_confirm_order_of_another_supplier(): void
    {
        $orderB = PurchaseOrder::create([
            'supplier_id' => $this->supplierB->id,
            'po_number' => 'PO-OTHER-VENDOR',
            'status' => 'sent',
            'priority' => 'normal',
            'total_units' => 60,
            'subtotal' => 9000.00,
            'tax_amount' => 1440.00,
            'total_amount' => 10440.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->postJson("/api/v1/hub7/vendor/orders/{$orderB->id}/confirm");
        $response->assertStatus(403);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $orderB->id,
            'status' => 'sent',
        ]);
    }

    /**
     * Verifica que un proveedor pueda visualizar la orden de compra imprimible / PDF.
     */
    public function test_vendor_can_print_order_document(): void
    {
        $product = Product::create([
            'title' => 'Arnes Pectoral T-Combat',
            'slug' => 'arnes-pectoral-t-combat',
            'sku' => 'SKU-ARN-001',
            'price' => 599.00,
            'stock' => 50,
        ]);

        $orderA = PurchaseOrder::create([
            'supplier_id' => $this->supplierA->id,
            'po_number' => 'PO-PRINTABLE-01',
            'status' => 'sent',
            'priority' => 'urgent',
            'total_units' => 30,
            'subtotal' => 4500.00,
            'tax_amount' => 720.00,
            'total_amount' => 5220.00,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $orderA->id,
            'product_id' => $product->id,
            'sku' => 'SKU-ARN-001-L',
            'quantity_ordered' => 30,
            'unit_cost' => 150.00,
            'subtotal' => 4500.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->get("/admin/extranet/proveedores/ordenes/{$orderA->id}/imprimir");

        $response->assertStatus(200);
        $response->assertSee('PO-PRINTABLE-01');
        $response->assertSee('Documento Oficial B2B');
        $response->assertSee('Arnes Pectoral T-Combat');
    }

    /**
     * Verifica que un proveedor no pueda imprimir la orden de otro fabricante.
     */
    public function test_vendor_cannot_print_order_of_another_supplier(): void
    {
        $orderB = PurchaseOrder::create([
            'supplier_id' => $this->supplierB->id,
            'po_number' => 'PO-PRINTABLE-B',
            'status' => 'sent',
            'priority' => 'normal',
            'total_units' => 40,
            'subtotal' => 6000.00,
            'tax_amount' => 960.00,
            'total_amount' => 6960.00,
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->get("/admin/extranet/proveedores/ordenes/{$orderB->id}/imprimir");
        $response->assertStatus(403);
    }

    /**
     * Verifica que la API de notas de credito y minutas solo retorne las del proveedor autenticado.
     */
    public function test_vendor_api_get_credit_agreements_returns_only_own_credits(): void
    {
        SupplierCreditAgreement::create([
            'supplier_id' => $this->supplierA->id,
            'reference_folio' => 'NC-VALLE-001',
            'agreement_type' => 'shortage_discount',
            'amount' => 1500.00,
            'status' => 'pending',
            'resolution_notes' => 'Faltante de 10 piezas en lote anterior',
        ]);

        SupplierCreditAgreement::create([
            'supplier_id' => $this->supplierB->id,
            'reference_folio' => 'NC-AZTECA-001',
            'agreement_type' => 'quality_defect',
            'amount' => 2800.00,
            'status' => 'pending',
            'resolution_notes' => 'Manchas en teñido lote B',
        ]);

        $this->actingAs($this->vendorUserA);

        $response = $this->getJson('/api/v1/hub7/vendor/credit-agreements');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'supplier_id' => $this->supplierA->id,
            'agreements_count' => 1,
        ]);

        $agreements = $response->json('agreements');
        $this->assertEquals('NC-VALLE-001', $agreements[0]['reference_folio']);
    }
}
