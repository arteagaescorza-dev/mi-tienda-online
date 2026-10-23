<?php

namespace Tests\Feature\RevOps;

use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\OperationalTask;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\RmaInspectionLog;
use App\Models\RmaRequest;
use App\Models\RmaResolution;
use App\Models\User;
use App\Services\RevOps\RmaResolutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossHubRmaResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createProductWithInventory(string $sku, int $initialQty = 10): array
    {
        $product = Product::create([
            'sku' => 'PROD-' . $sku,
            'title' => 'Boxer Modal Pima Premium',
            'slug' => 'boxer-modal-pima-' . strtolower($sku),
            'brand' => 'Torzo',
            'visible' => true,
            'price' => 399.00,
            'cost' => 120.00,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => 399.00,
            'cost' => 120.00,
            'visible' => true,
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => $initialQty,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        return [$product, $variant, $inventory];
    }

    public function test_sealed_hygiene_return_allows_restock_and_generates_replacement_order(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithInventory('TEST-BOXER-M', 10);

        $order = Order::create([
            'order_number' => '#ORD-2026-9001',
            'status' => 'delivered',
            'total' => 399.00,
            'billing_address' => ['name' => 'Carlos Mendoza'],
            'shipping_address' => ['name' => 'Carlos Mendoza', 'city' => 'CDMX'],
        ]);

        $rma = RmaRequest::create([
            'rma_number' => 'RMA-2026-001',
            'order_id' => $order->id,
            'customer_name' => 'Carlos Mendoza',
            'customer_phone' => '5511223344',
            'product_sku' => 'TEST-BOXER-M',
            'product_name' => 'Boxer Modal Pima Premium',
            'reason' => 'sizing_wrong',
            'sanitary_status' => 'pending_inspection',
            'status' => 'open',
        ]);

        $service = new RmaResolutionService();
        $result = $service->inspectAndResolve($rma, [
            'inspector_name' => 'Inspector Almacen Central',
            'has_hygiene_seal_intact' => true,
            'has_factory_tag' => true,
            'verdict' => 'approved',
            'resolution_type' => 'replacement_order',
            'replacement_sku' => 'TEST-BOXER-M',
            'technical_notes' => 'Prenda con precinto termosellado original sin alterar.',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('approved_replacement', $result['sanitary_status']);
        $this->assertEquals('resolved', $result['status']);

        // 1. Verificar reingreso comercial al Kardex
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 1,
            'reason' => 'ENTRADA_DEVOLUCION_RMA - Reingreso comercial autorizado (sello higienico integro) - Folio RMA-2026-001',
        ]);

        // 2. Verificar salida de reemplazo en Kardex
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => -1,
        ]);

        // El stock neto final debe ser 10 (+1 reingreso -1 salida)
        $inventory->refresh();
        $this->assertEquals(10, $inventory->quantity);

        // 3. Verificar orden de reemplazo #ORD-XXXX-RMA a $0.00
        $this->assertDatabaseHas('orders', [
            'order_number' => '#ORD-2026-9001-RMA',
            'total' => 0.00,
            'order_type' => 'rma_replacement',
            'rma_request_id' => $rma->id,
        ]);

        // 4. Verificar tarea operativa para empaque en almacen
        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'almacen',
            'priority' => 'urgente',
        ]);

        // 5. Verificar registro inmutable en rma_resolutions
        $this->assertDatabaseHas('rma_resolutions', [
            'rma_request_id' => $rma->id,
            'resolution_type' => 'replacement_order',
            'replacement_order_number' => '#ORD-2026-9001-RMA',
            'whatsapp_message_sent' => true,
        ]);

        // 6. Verificar actualizacion en rma_requests
        $rma->refresh();
        $this->assertEquals('resolved', $rma->status);
        $this->assertEquals('approved_replacement', $rma->sanitary_status);
        $this->assertEquals('#ORD-2026-9001-RMA', $rma->replacement_order_number);
    }

    public function test_broken_hygiene_seal_strictly_blocks_commercial_restock_and_scraps_as_merma(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithInventory('TEST-INTIMATE-01', 5);

        $rma = RmaRequest::create([
            'rma_number' => 'RMA-2026-002',
            'customer_name' => 'Alejandro Soto',
            'customer_phone' => '5599887766',
            'product_sku' => 'TEST-INTIMATE-01',
            'product_name' => 'Tanga Velvet Microfibra',
            'reason' => 'sizing_wrong',
            'sanitary_status' => 'pending_inspection',
            'status' => 'open',
        ]);

        $service = new RmaResolutionService();

        // Intento de aprobar pero con precinto violado (has_hygiene_seal_intact = false)
        $result = $service->inspectAndResolve($rma, [
            'inspector_name' => 'Inspector Sanitario',
            'has_hygiene_seal_intact' => false,
            'has_factory_tag' => false,
            'verdict' => 'approved', // Sera forzado a rejected por regla PROFECO
            'resolution_type' => 'replacement_order',
            'technical_notes' => 'El cliente retiro el sello de seguridad plastico.',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('rejected_used_hygiene', $result['sanitary_status']);
        $this->assertEquals('rejected', $result['status']);

        // El stock vendible NUNCA debe incrementarse: permanece en 5
        $inventory->refresh();
        $this->assertEquals(5, $inventory->quantity);

        // Debe existir el asiento de baja como merma sanitaria
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 0,
            'reason' => 'SALIDA_MERMA_RMA - Destruccion por vulneracion de precinto sanitario PROFECO - Folio RMA-2026-002',
        ]);

        // No se debe generar orden de reemplazo
        $this->assertDatabaseMissing('orders', [
            'rma_request_id' => $rma->id,
        ]);

        // Se registra la resolucion de rechazo sanitario
        $this->assertDatabaseHas('rma_resolutions', [
            'rma_request_id' => $rma->id,
            'resolution_type' => 'rejected_sanitary_violation',
            'whatsapp_message_sent' => true,
        ]);

        $rma->refresh();
        $this->assertEquals('rejected', $rma->status);
        $this->assertEquals('rejected_used_hygiene', $rma->sanitary_status);
    }

    public function test_store_credit_resolution_generates_single_use_coupon_and_tokenized_url(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithInventory('TEST-SWIM-01', 8);

        $rma = RmaRequest::create([
            'rma_number' => 'RMA-2026-003',
            'customer_name' => 'Mauricio Ramos',
            'customer_phone' => '5577881122',
            'product_sku' => 'TEST-SWIM-01',
            'product_name' => 'Traje de Bano Deportivo',
            'reason' => 'sizing_wrong',
            'refund_or_credit_amount' => 599.00,
            'sanitary_status' => 'pending_inspection',
            'status' => 'open',
        ]);

        $service = new RmaResolutionService();
        $result = $service->inspectAndResolve($rma, [
            'inspector_name' => 'Mariana Ruiz',
            'has_hygiene_seal_intact' => true,
            'has_factory_tag' => true,
            'verdict' => 'approved',
            'resolution_type' => 'store_credit',
            'credit_amount' => 599.00,
            'technical_notes' => 'Devolucion limpia con precinto intacto. No hay talla deseada en stock.',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('approved_store_credit', $result['sanitary_status']);
        $this->assertEquals('store_credit', $result['resolution_type']);

        // Verificar cupon digital creado en CRM
        $this->assertNotNull($result['coupon']['code']);
        $couponCode = $result['coupon']['code'];
        $this->assertStringStartsWith('CR-RMA2026003-', $couponCode);

        $this->assertDatabaseHas('coupons', [
            'code' => $couponCode,
            'discount_type' => 'fixed_amount',
            'discount_value' => 599.00,
            'max_uses' => 1,
            'is_active' => true,
        ]);

        // Verificar resolucion y enlace tokenizado
        $this->assertDatabaseHas('rma_resolutions', [
            'rma_request_id' => $rma->id,
            'resolution_type' => 'store_credit',
            'store_credit_code' => $couponCode,
            'store_credit_amount' => 599.00,
        ]);

        $this->assertStringContainsString($couponCode, $result['coupon']['tokenized_url']);
        $this->assertStringContainsString('Mauricio+Ramos', $result['coupon']['tokenized_url']);

        $rma->refresh();
        $this->assertEquals('resolved', $rma->status);
        $this->assertEquals('approved_store_credit', $rma->sanitary_status);
        $this->assertEquals($couponCode, $rma->store_credit_code);
    }

    public function test_portal_logistics_web_inspect_rma_controller_action(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        [$product, $variant, $inventory] = $this->createProductWithInventory('TEST-WEB-01', 12);

        $rma = RmaRequest::create([
            'rma_number' => 'RMA-WEB-01',
            'customer_name' => 'Daniela Castro',
            'customer_phone' => '5544332211',
            'product_sku' => 'TEST-WEB-01',
            'product_name' => 'Bralette Encaje',
            'reason' => 'sizing_wrong',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->post("/admin/logistica/rma/{$rma->id}/inspeccionar", [
            'inspector_name' => 'Inspector Portal Web',
            'has_hygiene_seal_intact' => '1',
            'has_factory_tag' => '1',
            'verdict' => 'approved',
            'resolution_type' => 'replacement_order',
            'replacement_sku' => 'TEST-WEB-01',
            'technical_notes' => 'Inspeccion completa desde panel web.',
        ]);

        $response->assertRedirect(route('portal.logistics'));
        $response->assertSessionHas('success');

        $rma->refresh();
        $this->assertEquals('resolved', $rma->status);
        $this->assertEquals('approved_replacement', $rma->sanitary_status);
        $this->assertNotNull($rma->replacement_order_number);
    }

    public function test_api_logistics_endpoints_for_rma_resolution(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        [$product, $variant, $inventory] = $this->createProductWithInventory('TEST-API-01', 20);

        $rma = RmaRequest::create([
            'rma_number' => 'RMA-API-RESOLVE',
            'customer_name' => 'Fernando Torres',
            'customer_phone' => '5566778899',
            'product_sku' => 'TEST-API-01',
            'product_name' => 'Suspensorio Deportivo',
            'reason' => 'sizing_wrong',
            'status' => 'open',
        ]);

        // 1. Endpoint general de resolucion
        $res = $this->actingAs($user)->postJson("/api/v1/logistics/rma/{$rma->id}/resolve", [
            'resolution_type' => 'store_credit',
            'has_hygiene_seal_intact' => true,
            'has_factory_tag' => true,
            'credit_amount' => 450.00,
            'technical_notes' => 'Aprobado via API con credito en monedero.',
        ]);

        $res->assertOk();
        $res->assertJsonPath('success', true);
        $res->assertJsonPath('resolution_type', 'store_credit');
        $res->assertJsonPath('sanitary_status', 'approved_store_credit');

        // 2. Endpoint de reposicion directa
        $rma2 = RmaRequest::create([
            'rma_number' => 'RMA-API-REP',
            'customer_name' => 'Gabriel Ortiz',
            'customer_phone' => '5511002233',
            'product_sku' => 'TEST-API-01',
            'product_name' => 'Suspensorio Deportivo',
            'reason' => 'factory_defect',
            'status' => 'open',
        ]);

        $resRep = $this->actingAs($user)->postJson("/api/v1/logistics/rma/{$rma2->id}/replacement-order", [
            'replacement_sku' => 'TEST-API-01',
            'technical_notes' => 'Reposicion automatica via API.',
        ]);

        $resRep->assertStatus(201);
        $resRep->assertJsonPath('success', true);
        $resRep->assertJsonPath('resolution_type', 'replacement_order');
        $this->assertNotNull($resRep->json('replacement_order.order_number'));
    }
}
