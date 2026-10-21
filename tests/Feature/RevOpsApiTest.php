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
use App\Models\ScrapedPendingItem;
use App\Models\Coupon;
use App\Models\CustomerSubscription;
use App\Models\RmaRequest;
use App\Models\Ally;
use App\Models\AllyLeadCapture;
use App\Models\OperationalTask;

class RevOpsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_api_endpoints(): void
    {
        $supplier = Supplier::create(['name' => 'Torzo', 'code' => 'SUP-TORZO']);
        $product = Product::create(['title' => 'Boxer Modal', 'slug' => 'boxer-modal', 'sku' => 'BOX-MOD-01', 'price' => 299.00]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'BOX-MOD-01-M', 'title' => 'M', 'price' => 299.00]);
        $inventory = Inventory::create(['variant_id' => $variant->id, 'location' => 'warehouse-1', 'quantity' => 5, 'reserved' => 0]);

        $po = PurchaseOrder::create(['po_number' => 'OC-API-01', 'supplier_id' => $supplier->id, 'status' => 'confirmed', 'total_units' => 10]);
        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 10,
            'unit_cost' => 100.00,
        ]);

        // 1. Recibir OC vía API
        $response = $this->postJson("/api/v1/inventory/purchases/orders/{$po->id}/receive", [
            'items' => [$item->id => 10],
            'notes' => 'Recibido en muelle',
        ]);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'received');

        // 2. Curar ítem de scraping
        $scraped = ScrapedPendingItem::create([
            'supplier_id' => $supplier->id,
            'external_sku' => 'TORZO-991',
            'external_title' => 'Arnés Cuero Neón',
            'external_price' => 250.00,
            'curation_status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/inventory/scraping/pending/{$scraped->id}/curate", [
            'action' => 'approve',
            'curated_title' => 'Arnés Cuero Neón Marte',
            'curated_price' => 599.00,
            'curated_brand' => 'Marte',
        ]);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'approved');

        // 3. Auditoría cíclica
        $response = $this->postJson('/api/v1/inventory/cycle-counts', [
            'counts' => [
                ['variant_id' => $variant->id, 'physical_counted' => 15],
            ],
        ]);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('ira_accuracy_score', 100);
    }

    public function test_checkout_stock_reservation_api(): void
    {
        $product = Product::create(['title' => 'Jockstrap Glow', 'slug' => 'jockstrap-glow', 'sku' => 'JCK-GLOW', 'price' => 349.00]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'JCK-GLOW-G', 'title' => 'G', 'price' => 349.00]);
        Inventory::create(['variant_id' => $variant->id, 'location' => 'warehouse-1', 'quantity' => 10, 'reserved' => 0]);

        // Reservar
        $res = $this->postJson('/api/v1/checkout/reserve-stock', [
            'cart_id' => 'cart_api_123',
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'active');

        $lockId = $res->json('lock_id');

        // Liberar
        $res = $this->postJson('/api/v1/checkout/release-stock', [
            'lock_id' => $lockId,
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_marketing_and_coupon_api(): void
    {
        Coupon::create([
            'code' => 'DESCUENTO15',
            'discount_type' => 'percent',
            'discount_value' => 15.00,
            'min_order_amount' => 500.00,
            'target_universe' => 'marte',
        ]);

        // Cupón válido
        $res = $this->postJson('/api/v1/marketing/coupons/validate', [
            'code' => 'DESCUENTO15',
            'order_amount' => 1000.00,
            'universe' => 'marte',
        ]);
        $res->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('discount_value', 150);

        // Cupón con monto insuficiente
        $res = $this->postJson('/api/v1/marketing/coupons/validate', [
            'code' => 'DESCUENTO15',
            'order_amount' => 300.00,
            'universe' => 'marte',
        ]);
        $res->assertStatus(422)
            ->assertJsonPath('valid', false);

        // Lead magnet capture
        $res = $this->postJson('/api/v1/marketing/lead-magnet/capture', [
            'name' => 'Roberto Carlos',
            'email' => 'roberto.c@example.com',
            'phone' => '+525544332211',
            'universe' => 'marte',
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('capi_event_match_score', 9.8);

        // Chatbot message
        $res = $this->postJson('/api/v1/marketing/chatbot/message', [
            'customer_name' => 'Juan Perez',
            'message' => '¿Cuál es mi talla si mido 32 de cintura?',
            'sender' => 'user',
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_finance_dunning_api(): void
    {
        $sub = CustomerSubscription::create([
            'subscription_folio' => 'SUB-FIN-01',
            'tier_name' => 'Caja Marte VIP',
            'universe' => 'marte',
            'monthly_amount' => 999.00,
            'status' => 'active',
        ]);

        $res = $this->postJson("/api/v1/finance/dunning/{$sub->id}/retry");
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'past_due');

        $res = $this->postJson("/api/v1/finance/dunning/{$sub->id}/recover", [
            'amount_recovered' => 999.00,
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'active');
    }

    public function test_logistics_manifest_and_rma_api(): void
    {
        // Generar manifiesto
        $res = $this->postJson('/api/v1/logistics/manifests', [
            'courier' => 'dhl_express',
            'driver_name' => 'Carlos Mendoza',
            'driver_badge' => 'DHL-552',
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'signed');

        // Inspección RMA
        $rma = RmaRequest::create([
            'rma_number' => 'RMA-API-01',
            'customer_name' => 'Laura Garza',
            'product_sku' => 'VEN-BRA-001',
            'product_name' => 'Bralette Encaje',
            'reason' => 'sizing_wrong',
        ]);

        $res = $this->postJson("/api/v1/logistics/rma/{$rma->id}/inspect", [
            'verdict' => 'approved_store_credit',
            'has_hygiene_seal_intact' => true,
            'has_factory_tag' => true,
            'inspector_name' => 'Mariana Ruiz',
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sanitary_status', 'approved_store_credit');
    }

    public function test_allies_api_endpoints(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin);

        $ally = Ally::create([
            'code' => 'ALLY-API-TEST',
            'name' => 'Club Nocturno Test',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'portal_token' => 'tok_api_test',
            'lead_acquisition_cost' => 20.00,
        ]);

        $lead = AllyLeadCapture::create([
            'voucher_code' => 'API-VOUCHER-01',
            'ally_id' => $ally->id,
            'lead_name' => 'Alejandro Morales',
            'lead_whatsapp' => '+525511223344',
            'universe' => 'marte',
            'is_redeemed' => false,
        ]);

        // Validar voucher en taquilla
        $res = $this->postJson('/api/v1/allies/validate-voucher', [
            'voucher_code' => 'API-VOUCHER-01',
            'redeemed_by' => 'Staff Taquilla #1',
        ]);
        $res->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('status', 'success');

        // Reintentar validar el mismo voucher (debe alertar duplicado)
        $res = $this->postJson('/api/v1/allies/validate-voucher', [
            'voucher_code' => 'API-VOUCHER-01',
        ]);
        $res->assertStatus(422)
            ->assertJsonPath('valid', false)
            ->assertJsonPath('status', 'already_redeemed');

        // Exportar CSV
        $res = $this->get("/api/v1/allies/{$ally->id}/export-csv");
        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_dashboard_tasks_and_environment_api(): void
    {
        $task = OperationalTask::create([
            'title' => 'Conciliar remesa de transportista',
            'category' => 'logistics',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $res = $this->postJson("/api/v1/dashboard/tasks/{$task->id}/complete");
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'completed');

        $res = $this->postJson('/api/v1/dashboard/environment/switch', [
            'module' => 'stripe_payments',
            'target_environment' => 'sandbox',
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('active_environment', 'sandbox');

        $res = $this->postJson('/api/v1/dashboard/copilot/chat', [
            'message' => '¿Cuál es el estado de la operación de hoy?',
        ]);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['reply', 'action_chips']);
    }
}
