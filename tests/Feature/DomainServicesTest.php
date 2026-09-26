<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockLock;
use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyCouponDirB;
use App\Models\CustomerSubscription;
use App\Models\VendorBill;
use App\Services\InboundReceiptService;
use App\Services\StockReservationService;
use App\Services\ClearingHouseSettlementService;
use App\Services\DunningRecoveryService;

class DomainServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbound_receipt_service_recalculates_cpp_and_updates_kardex(): void
    {
        $supplier = Supplier::create([
            'name' => 'Torzo Mayorista Textil',
            'code' => 'SUP-TORZO',
        ]);

        $product = Product::create([
            'title' => 'Boxer Clásico Algodón Pima',
            'slug' => 'boxer-clasico-algodon-pima',
            'sku' => 'MAR-BOX-001',
            'type' => 'dropship', // Debe transicionar a mixto tras recibir inventario
            'cost' => 100.00,
            'price' => 299.00,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'MAR-BOX-001-M',
            'title' => 'Mediana (M)',
            'cost' => 100.00,
            'price' => 299.00,
        ]);

        // Stock inicial: 10 unidades a $100 CPP
        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'OC-202609-INB1',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 20,
            'total_amount' => 2600.00,
        ]);

        // Nueva compra: 20 unidades a $130
        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 20,
            'quantity_received' => 0,
            'unit_cost' => 130.00,
            'total_cost' => 2600.00,
        ]);

        $service = app(InboundReceiptService::class);
        $result = $service->receivePurchaseOrder($po, [$poItem->id => 20], null, 'Recepción completa lote 1');

        $this->assertTrue($result['success']);
        $this->assertEquals('received', $result['status']);
        $this->assertEquals(20, $result['units_received']);

        // Verificación de nuevo inventario físico: 10 + 20 = 30
        $inventory->refresh();
        $this->assertEquals(30, $inventory->quantity);

        // Verificación de CPP: (10 * 100 + 20 * 130) / 30 = (1000 + 2600) / 30 = 3600 / 30 = 120.00
        $variant->refresh();
        $product->refresh();
        $this->assertEquals(120.00, (float) $variant->cost);
        $this->assertEquals(120.00, (float) $product->cost);

        // Verificación de transición de tipo dropship -> both (mixto)
        $this->assertEquals('both', $product->type);

        // Verificación de asentamiento en Kardex
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 20,
            'quantity_before' => 10,
            'quantity_after' => 30,
        ]);

        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $po->id,
            'movement_type' => 'INBOUND_RECEIPT',
        ]);
    }

    public function test_stock_reservation_service_locks_and_releases_stock(): void
    {
        $product = Product::create([
            'title' => 'Suspensorio Marte Red',
            'slug' => 'suspensorio-marte-red',
            'sku' => 'MAR-SUSP-001',
            'price' => 349.00,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'MAR-SUSP-001-CH',
            'title' => 'Chica (CH)',
            'price' => 349.00,
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $service = app(StockReservationService::class);
        $cartId = 'cart_test_session_123';

        // 1. Reservar 3 unidades
        $lock = $service->reserveStock($cartId, $variant->id, 3, null, 15);
        $this->assertEquals('active', $lock->status);

        $inventory->refresh();
        $this->assertEquals(3, $inventory->reserved);

        // 2. Comprometer en compra (checkout finalizado)
        $service->commitStockOnPurchase($cartId, 9001);

        $inventory->refresh();
        $this->assertEquals(7, $inventory->quantity);
        $this->assertEquals(0, $inventory->reserved);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => -3,
            'quantity_before' => 10,
            'quantity_after' => 7,
        ]);

        // 3. Probar limpieza de locks expirados
        $expiredLock = StockLock::create([
            'cart_id' => 'cart_abandoned_999',
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'expires_at' => now()->subMinutes(5),
            'status' => 'active',
        ]);
        $inventory->reserved = 2;
        $inventory->save();

        $cleanedCount = $service->cleanupExpiredLocks();
        $this->assertEquals(1, $cleanedCount);

        $inventory->refresh();
        $this->assertEquals(0, $inventory->reserved);
    }

    public function test_clearing_house_settlement_service_calculates_balance_and_generates_bill(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-PARADISO-TEST',
            'name' => 'Paradiso Circuit Eventos',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'portal_token' => 'tok_paradiso_test',
            'commission_rate' => 12.00,
            'lead_acquisition_cost' => 20.00,
            'bank_clabe' => '012180015678901234',
            'bank_name' => 'BBVA México',
            'clearing_balance' => -4000.00,
        ]);

        // Campaña con 100 leads registrados ($20 * 100 = $2,000 MXN a favor de la tienda)
        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => 'Cover 20% Paradiso',
            'slug' => 'cover-paradiso-test',
            'universe' => 'marte',
            'benefit' => '20% OFF',
            'leads_count' => 100,
        ]);

        // Cupón con $6,000 en comisiones ganadas ($6,000 a favor del aliado)
        $coupon = AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'PARADISOTEST15',
            'discount_pct' => 15.00,
            'commission_rate' => 12.00,
            'universe' => 'marte',
            'utm_source' => 'paradiso',
            'utm_campaign' => 'mayo_2026',
            'commission_earned' => 6000.00,
        ]);

        $service = app(ClearingHouseSettlementService::class);
        $calc = $service->calculateNetBalance($ally);

        // Saldo neto: $2,000 - $6,000 = -$4,000 MXN (Por liquidar al aliado)
        $this->assertEquals(2000.00, $calc['leads_credit']);
        $this->assertEquals(6000.00, $calc['commissions_debit']);
        $this->assertEquals(-4000.00, $calc['net_balance']);

        // Ejecutar liquidación vía SPEI
        $settlement = $service->executeSettlement($ally, 'spei_transfer', 'Liquidación mensual corte mayo');

        $this->assertEquals('executed', $settlement->status);
        $this->assertEquals(-4000.00, (float) $settlement->net_balance);

        // Verificar inyección automática a Cuentas por Pagar (TASK-INT-19)
        $this->assertDatabaseHas('vendor_bills', [
            'vendor_type' => 'ally_settlement',
            'ally_id' => $ally->id,
            'amount' => 4000.00,
            'status' => 'scheduled',
        ]);
    }

    public function test_dunning_recovery_service_escalates_and_recovers_subscription(): void
    {
        $sub = CustomerSubscription::create([
            'subscription_folio' => 'SUB-MARTE-TEST-01',
            'tier_name' => 'Caja Marte Nivel 1',
            'universe' => 'marte',
            'monthly_amount' => 599.00,
            'status' => 'active',
            'failed_attempts_count' => 0,
        ]);

        $service = app(DunningRecoveryService::class);

        // 1. Primer fallo de cobro
        $log1 = $service->handlePaymentDecline($sub, 'insufficient_funds');
        $sub->refresh();
        $this->assertEquals(1, $sub->failed_attempts_count);
        $this->assertEquals('past_due', $sub->status);
        $this->assertEquals('automated_charge_retry_scheduled', $log1->action_taken);

        // 2. Segundo fallo de cobro
        $log2 = $service->handlePaymentDecline($sub, 'card_expired');
        $sub->refresh();
        $this->assertEquals(2, $sub->failed_attempts_count);
        $this->assertEquals('whatsapp_link_sent', $log2->action_taken);

        // 3. Recuperación exitosa
        $service->markAsRecovered($sub, 599.00);
        $sub->refresh();
        $this->assertEquals(0, $sub->failed_attempts_count);
        $this->assertEquals('active', $sub->status);
    }
}
