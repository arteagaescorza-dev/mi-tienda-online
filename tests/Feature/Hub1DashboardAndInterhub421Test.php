<?php

namespace Tests\Feature;

use App\Events\Inventory\PurchaseOrderReceived;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OperationalTask;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\ShiftLog;
use App\Models\StockLock;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Hub1DashboardAndInterhub421Test extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin.hub1@mitienda.online',
        ]);
    }

    public function test_hub1_dashboard_segregates_physical_ready_from_ondemand_orders_without_false_minimums(): void
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor Textil Central',
            'contact_email' => 'central@proveedor.test',
        ]);

        // 1. Producto físico local
        $physicalProduct = Product::create([
            'sku' => 'TEST-PHYS-01',
            'slug' => 'boxer-microfibra-clasico-test-phys-01',
            'title' => 'Boxer Microfibra Clásico',
            'price' => 250.00,
            'cost' => 100.00,
            'type' => 'physical',
        ]);
        $physVariant = ProductVariant::create([
            'product_id' => $physicalProduct->id,
            'sku' => 'TEST-PHYS-01-M',
            'price' => 250.00,
            'cost' => 100.00,
            'attributes' => ['size' => 'M'],
        ]);

        // 2. Producto bajo pedido / dropship
        $dropshipProduct = Product::create([
            'sku' => 'TEST-DROP-01',
            'slug' => 'pijama-satin-importada-test-drop-01',
            'title' => 'Pijama Satin Importada',
            'price' => 450.00,
            'cost' => 200.00,
            'type' => 'dropship',
        ]);
        $dropVariant = ProductVariant::create([
            'product_id' => $dropshipProduct->id,
            'sku' => 'TEST-DROP-01-L',
            'price' => 450.00,
            'cost' => 200.00,
            'attributes' => ['size' => 'L'],
        ]);

        // 3. Orden A: 100% stock físico en bodega
        $orderA = Order::create([
            'order_number' => 'ORD-PHYS-001',
            'user_id' => $this->adminUser->id,
            'total' => 250.00,
            'status' => 'processing',
        ]);
        OrderItem::create([
            'order_id' => $orderA->id,
            'variant_id' => $physVariant->id,
            'quantity' => 1,
            'unit_price' => 250.00,
            'line_total' => 250.00,
        ]);

        // 4. Orden B: Contiene prenda bajo pedido (dropship)
        $orderB = Order::create([
            'order_number' => 'ORD-DROP-002',
            'user_id' => $this->adminUser->id,
            'total' => 450.00,
            'status' => 'processing',
        ]);
        OrderItem::create([
            'order_id' => $orderB->id,
            'variant_id' => $dropVariant->id,
            'quantity' => 1,
            'unit_price' => 450.00,
            'line_total' => 450.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('portal.dashboard'));
        $response->assertOk();

        // Extraer kpis enviados a la vista
        $kpis = $response->viewData('kpis');
        $this->assertNotNull($kpis);
        $this->assertArrayHasKey('operative', $kpis);

        $packingKpi = $kpis['operative'][0];
        $this->assertEquals('Pedidos por empacar', $packingKpi['label']);
        // Debe ser exactamente 2 órdenes (sin el mínimo artificial de 4)
        $this->assertEquals('2 órdenes', $packingKpi['value']);
        // Subtítulo debe segregar físico vs bajo pedido
        $this->assertStringContainsString('1 en rack físico', $packingKpi['subtitle']);
        $this->assertStringContainsString('1 bajo pedido', $packingKpi['subtitle']);
    }

    public function test_operational_task_sla_distinguishes_courier_packing_from_ondemand_and_purchases(): void
    {
        // Tarea de empaque físico por vencer en 30 min -> Crítica (Corte DHL inminente)
        $whTask = OperationalTask::create([
            'title' => 'Empaque Pedido #ORD-PHYS-001',
            'reference_code' => '#ORD-PHYS-001',
            'category' => 'empaque',
            'assigned_role' => 'warehouse',
            'status' => 'pending',
            'due_at' => now()->addMinutes(30),
        ]);
        $this->assertEquals('critical', $whTask->sla_status);

        // Tarea de compras / inbound por vencer en 30 min -> No debe causar falso positivo de empaque DHL
        $purchasesTask = OperationalTask::create([
            'title' => 'Validar recepción mayorista Torzo',
            'reference_code' => '#OC-2026-003',
            'category' => 'inbound',
            'assigned_role' => 'purchases',
            'status' => 'pending',
            'due_at' => now()->addMinutes(30),
        ]);
        $this->assertEquals('ontime', $purchasesTask->sla_status);

        // Tarea de compras vencida hace 10 min -> Warning (atención de compras), NO critical de paquetería
        $overduePurchasesTask = OperationalTask::create([
            'title' => 'Seguimiento fletero proveedor externo',
            'reference_code' => '#OC-2026-004',
            'category' => 'compras',
            'assigned_role' => 'purchases',
            'status' => 'pending',
            'due_at' => now()->subMinutes(10),
        ]);
        $this->assertEquals('warning', $overduePurchasesTask->sla_status);
    }

    public function test_po_received_listener_completes_all_associated_tasks_with_or_without_hash_prefix(): void
    {
        $supplier = Supplier::create([
            'name' => 'Confecciones del Norte',
            'contact_email' => 'norte@textil.test',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'OC-2026-888',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'total_units' => 100,
            'subtotal' => 10000.00,
            'total' => 11600.00,
        ]);

        // Tarea Almacén con prefijo '#'
        $whTask = OperationalTask::create([
            'title' => 'Recepción Inbound: Confecciones #OC-2026-888',
            'reference_code' => '#OC-2026-888',
            'category' => 'recepcion',
            'assigned_role' => 'warehouse',
            'status' => 'pending',
            'action_payload' => ['po_number' => 'OC-2026-888'],
        ]);

        // Tarea Compras sin prefijo '#'
        $purchTask = OperationalTask::create([
            'title' => 'Cotejo y Facturación: Confecciones OC-2026-888',
            'reference_code' => 'OC-2026-888',
            'category' => 'compras',
            'assigned_role' => 'purchases',
            'status' => 'pending',
            'action_payload' => ['purchase_order_id' => $po->id],
        ]);

        // Disparar evento de recepción total
        event(new PurchaseOrderReceived(
            purchaseOrder: $po,
            itemsReceived: [],
            totalUnitsReceived: 100,
            totalCostReceived: 10000.00,
            operatorId: $this->adminUser->id,
            sanitaryData: ['batch_number' => 'LOT-TEST-888'],
            isFullyReceived: true
        ));

        $whTask->refresh();
        $purchTask->refresh();

        $this->assertEquals('completed', $whTask->status);
        $this->assertNotNull($whTask->completed_at);
        $this->assertEquals($this->adminUser->name, $whTask->completed_by);

        $this->assertEquals('completed', $purchTask->status);
        $this->assertNotNull($purchTask->completed_at);
        $this->assertEquals($this->adminUser->name, $purchTask->completed_by);

        // Verificar logs de turno generados para ambos roles
        $logs = ShiftLog::whereIn('operational_task_id', [$whTask->id, $purchTask->id])->get();
        $this->assertCount(2, $logs);
    }

    public function test_interhub_stock_lock_visibility_and_breakdown_in_hub4_and_sku_api(): void
    {
        $product = Product::create([
            'sku' => 'BOX-MOD-BLK',
            'slug' => 'boxer-modal-negro-premium-box-mod-blk',
            'title' => 'Boxer Modal Negro Premium',
            'price' => 350.00,
            'cost' => 120.00,
            'type' => 'physical',
            'meta' => [
                'location' => 'Rack A-02 / Nivel 1',
                'category' => 'Ropa Interior',
            ],
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'BOX-MOD-BLK-M',
            'price' => 350.00,
            'cost' => 120.00,
            'attributes' => ['size' => 'M'],
        ]);

        // Existencia física: 20 unidades, 2 reservadas por orden
        Inventory::create([
            'variant_id' => $variantM->id,
            'location' => 'warehouse-1',
            'quantity' => 20,
            'reserved' => 2,
            'type' => 'physical',
        ]);

        // Apartado activo de checkout (StockLock): 3 unidades
        $lock = StockLock::create([
            'cart_id' => 'cart_session_test_123',
            'product_variant_id' => $variantM->id,
            'quantity' => 3,
            'status' => 'active',
            'expires_at' => now()->addMinutes(10),
        ]);

        // 1. Verificar carga en Hub 4 (portal.inventory)
        $response = $this->actingAs($this->adminUser)->get(route('portal.inventory'));
        $response->assertOk();

        $portalProducts = $response->viewData('portalProducts');
        $item = collect($portalProducts)->firstWhere('sku', 'BOX-MOD-BLK');

        $this->assertNotNull($item);
        $this->assertEquals(20, $item['physicalStock']);
        $this->assertEquals(2, $item['committedStock']);
        $this->assertEquals(3, $item['lockedStock']);
        // Disponible venta = Físico (20) - Comprometido (2) - En Locks (3) = 15
        $this->assertEquals(15, $item['localStock']);

        // 2. Verificar API SKU Detail
        $detailResponse = $this->actingAs($this->adminUser)->getJson("/api/v1/inventory/products/{$product->sku}/detail");
        $detailResponse->assertOk();

        $detailJson = $detailResponse->json('data');
        $this->assertEquals(20, $detailJson['stock']['physical_stock']);
        $this->assertEquals(2, $detailJson['stock']['committed_stock']);
        $this->assertEquals(3, $detailJson['stock']['locked_stock']);
        $this->assertEquals(15, $detailJson['stock']['available_stock']);
    }

    public function test_expired_stock_lock_is_not_counted_in_locked_stock(): void
    {
        $product = Product::create([
            'sku' => 'TEE-EXP-01',
            'slug' => 'playera-cuello-redondo-tee-exp-01',
            'title' => 'Playera Cuello Redondo',
            'price' => 299.00,
            'cost' => 90.00,
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEE-EXP-01-L',
            'price' => 299.00,
            'cost' => 90.00,
            'attributes' => ['size' => 'L'],
        ]);

        Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        // Lock expirado (hace 5 minutos)
        StockLock::create([
            'cart_id' => 'cart_session_expired',
            'product_variant_id' => $variant->id,
            'quantity' => 4,
            'status' => 'active',
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('portal.inventory'));
        $response->assertOk();

        $portalProducts = $response->viewData('portalProducts');
        $item = collect($portalProducts)->firstWhere('sku', 'TEE-EXP-01');

        $this->assertNotNull($item);
        $this->assertEquals(10, $item['physicalStock']);
        $this->assertEquals(0, $item['lockedStock']);
        $this->assertEquals(10, $item['localStock']);
    }
}
