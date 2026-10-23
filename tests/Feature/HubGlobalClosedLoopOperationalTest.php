<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\AllyCouponDirB;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderFinancialMargin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockLock;
use App\Models\Supplier;
use App\Models\ThreeWayMatching;
use App\Models\User;
use App\Models\VendorBill;
use App\Services\Finance\ThreeWayMatchingService;
use App\Services\OrderProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HubGlobalClosedLoopOperationalTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'portal',
            'email' => 'revops.global@mitienda.online',
            'name' => 'Superadmin Mickey RevOps',
        ]);
    }

    public function test_complete_closed_loop_across_hubs_4_2_1_7_and_5(): void
    {
        // =========================================================================
        // 1. HUB 4: ALTA Y CURADURÍA DE PRENDA CON INVENTARIO FÍSICO
        // =========================================================================
        $product = Product::create([
            'sku' => 'REV-GLB-001',
            'slug' => 'chamarra-biker-cuero-negro',
            'title' => 'Chamarra Biker Cuero Negro Multiverso',
            'price' => 1000.00,
            'cost' => 350.00,
            'type' => 'physical',
            'status' => 'published',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'REV-GLB-001-M',
            'price' => 1000.00,
            'cost' => 350.00,
            'attributes' => ['size' => 'M', 'color' => 'Negro'],
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 20,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $this->assertEquals(20, $inventory->quantity);

        // =========================================================================
        // 2. HUB 7: ACTIVACIÓN DE ALIADO COMERCIAL Y CUPÓN DIR B
        // =========================================================================
        $ally = Ally::create([
            'code' => 'ALLY-PARADISO-GLB',
            'name' => 'Paradiso Circuit CDMX',
            'category' => 'party_circuit',
            'portal_token' => 'tok_' . Str::random(16),
            'status' => 'active',
            'bank_clabe' => '012180004567890123',
            'bank_name' => 'BBVA México',
        ]);

        $coupon = AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'PARADISO20',
            'discount_pct' => 20.00,
            'commission_rate' => 10.00,
            'status' => 'active',
            'utm_source' => 'paradiso_fest',
            'utm_campaign' => 'verano_circuit',
        ]);

        // =========================================================================
        // 3. HUB 2: STOREFRONT & CHECKOUT CON BLOQUEO DE STOCK Y CUPÓN DE ALIADO
        // =========================================================================
        $cartSessionId = 'cart_glb_' . Str::random(10);
        $cart = Cart::create([
            'session_id' => $cartSessionId,
            'status' => 'active',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 1000.00,
        ]);

        // Bloqueo de Stock temporal durante checkout (15 min)
        $lock = StockLock::create([
            'cart_id' => $cartSessionId,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
        ]);

        $orderProcessing = app(OrderProcessingService::class);

        // Validar cupón: 2 x $1000 = $2000 subtotal -> 20% descuento = $400 desc
        $couponCheck = $orderProcessing->validateCoupon('PARADISO20', 2000.00);
        $this->assertTrue($couponCheck['valid']);
        $this->assertEquals(400.00, $couponCheck['discount_amount']);
        $this->assertEquals(160.00, $couponCheck['commission_estimated']); // 10% de $1600 base

        // Ejecución ACID de Checkout
        $order = $orderProcessing->processCheckout([
            'cart_id' => $cartSessionId,
            'customer_email' => 'asistente.paradiso@gmail.com',
            'customer_name' => 'Alejandro Rivera',
            'customer_phone' => '5541908812',
            'coupon_code' => 'PARADISO20',
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 2,
                    'unit_price' => 1000.00,
                ],
            ],
            'shipping_address' => [
                'street' => 'Av. Insurgentes Sur',
                'exterior_number' => '1602',
                'postal_code' => '03900',
                'colonia' => 'Crédito Constructor',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
            ],
            'payment_method' => 'credit_debit_card',
        ]);

        // =========================================================================
        // 4. INTERHUB 4 & 2: KARDEX, STOCK LOCKS Y MÁRGENES FINANCIEROS
        // =========================================================================
        // El lock debe transmutar a 'purchased'
        $lock->refresh();
        $this->assertEquals('purchased', $lock->status);

        // El inventario físico debe haberse reducido en 2 unidades (20 - 2 = 18)
        $inventory->refresh();
        $this->assertEquals(18, $inventory->quantity);

        // Kardex / InventoryMovement debe asentar la salida de almacén
        $movement = InventoryMovement::where('inventory_id', $inventory->id)->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-2, $movement->quantity);
        $this->assertEquals(20, $movement->quantity_before);
        $this->assertEquals(18, $movement->quantity_after);
        $this->assertStringContainsString('SALIDA_VENTA_ONLINE', $movement->reason);

        // Se debe haber creado el registro financiero OrderFinancialMargin
        $margin = OrderFinancialMargin::where('order_id', $order->id)->first();
        $this->assertNotNull($margin);
        $this->assertEquals(700.00, (float) $margin->cogs_total); // 2 unidades x $350 costo

        // =========================================================================
        // 5. HUB 1: DASHBOARD OPERATIVO Y LOGÍSTICA DHL
        // =========================================================================
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'payment_method' => 'credit_debit_card',
        ]);
        $this->assertNotNull($order->shipment);
        $this->assertStringStartsWith('DHL-MX-', $order->shipment->tracking_number);
        $this->assertEquals('dhl_express', $order->shipment->courier);

        // =========================================================================
        // 6. HUB 7: ATRIBUCIÓN AL ALIADO B2B EN CÁMARA DE COMPENSACIÓN
        // =========================================================================
        $coupon->refresh();
        $this->assertEquals(1, $coupon->orders_count);
        $this->assertEquals(1600.00, (float) $coupon->revenue_attributed);
        $this->assertEquals(160.00, (float) $coupon->commission_earned);

        // =========================================================================
        // 7. HUB 5: COTEJO DE 3 VÍAS Y DISPERSIÓN FISCAL SPEI CON BLINDAJE ANTIFRAUDE
        // =========================================================================
        $matchingService = app(ThreeWayMatchingService::class);
        $threeWay = $matchingService->evaluateInboundMatch(
            poCode: 'PO-GLB-2026-99',
            supplierId: 'VND-PARADISO-TEXTIL',
            supplierName: 'Taller Confección Central',
            poQty: 50,
            poTotal: 17500.00, // $350 / unit
            whReceivedQty: 50,
            whDamagedQty: 0,
            invoiceNumber: 'FAC-GLB-PERFECT-01',
            invoiceQty: 50,
            invoiceGross: 17500.00,
            inspectedBy: 'Rampa Central Mickey'
        );

        $this->assertEquals('matched', $threeWay->match_status);
        $this->assertFalse($threeWay->spei_payout_blocked);

        // Autorizar dispersión SPEI
        $approvedMatching = $matchingService->authorizeSpeiPayout($threeWay->id, 'SPEI-BBVA-99887766');
        $this->assertEquals('approved_for_payment', $approvedMatching->match_status);

        // La factura de proveedor asociada debe quedar marcada como pagada con su clave SPEI
        $this->assertDatabaseHas('vendor_bills', [
            'bill_number' => 'FAC-GLB-PERFECT-01',
            'status' => 'paid',
            'payment_reference' => 'SPEI-BBVA-99887766',
        ]);
    }
}
