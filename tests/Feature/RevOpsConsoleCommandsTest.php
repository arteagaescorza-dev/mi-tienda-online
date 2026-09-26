<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\StockLock;
use App\Models\CustomerSubscription;
use App\Models\DunningRetryLog;
use App\Models\ApiHealthLog;
use App\Models\OperationalTask;

class RevOpsConsoleCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_expired_stock_locks_command(): void
    {
        $product = Product::create(['title' => 'Boxer Microfibra', 'slug' => 'boxer-microfibra', 'sku' => 'BOX-MF-01', 'price' => 299.00]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'BOX-MF-01-M', 'title' => 'M', 'price' => 299.00]);
        $inventory = Inventory::create(['variant_id' => $variant->id, 'location' => 'warehouse-1', 'quantity' => 10, 'reserved' => 2]);

        StockLock::create([
            'cart_id' => 'cart_expired_999',
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'expires_at' => now()->subMinutes(10),
            'status' => 'active',
        ]);

        $this->artisan('stock:cleanup-expired-locks')
            ->expectsOutputToContain('Se liberaron con éxito 1 candados de stock expirados.')
            ->assertSuccessful();

        $inventory->refresh();
        $this->assertEquals(0, $inventory->reserved);
    }

    public function test_telemetry_check_apis_command(): void
    {
        $this->artisan('telemetry:check-apis')
            ->expectsOutputToContain('Telemetría de APIs registrada con éxito en api_health_logs.')
            ->assertSuccessful();

        $this->assertDatabaseHas('api_health_logs', ['service_name' => 'stripe', 'status_verdict' => 'healthy']);
        $this->assertDatabaseHas('api_health_logs', ['service_name' => 'meta_capi', 'status_verdict' => 'healthy']);
    }

    public function test_inventory_evaluate_rop_command(): void
    {
        $product = Product::create(['title' => 'Arnés Cuero Marte', 'slug' => 'arnes-cuero-marte', 'sku' => 'ARN-001', 'price' => 499.00]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'ARN-001-U', 'title' => 'Unitalla', 'price' => 499.00]);
        
        // Stock 2 disponible vs umbral 5 (Bajo ROP)
        Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 2,
            'reserved' => 0,
            'min_threshold' => 5,
        ]);

        $this->artisan('inventory:evaluate-rop')
            ->expectsOutputToContain('Evaluación de ROP finalizada. 1 alertas procesadas.')
            ->assertSuccessful();

        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'inventory_rop',
            'priority' => 'critical',
            'status' => 'pending',
        ]);
    }

    public function test_finance_process_dunning_command(): void
    {
        $sub = CustomerSubscription::create([
            'subscription_folio' => 'SUB-CMD-01',
            'tier_name' => 'Caja Marte Plus',
            'universe' => 'marte',
            'monthly_amount' => 799.00,
            'status' => 'past_due',
            'failed_attempts_count' => 1,
        ]);

        DunningRetryLog::create([
            'subscription_id' => $sub->id,
            'attempt_number' => 1,
            'decline_code' => 'insufficient_funds',
            'action_taken' => 'first_attempt',
            'was_recovered' => false,
            'scheduled_retry_at' => now()->subMinutes(5),
        ]);

        $this->artisan('finance:process-dunning')
            ->expectsOutputToContain('Procesamiento de Dunning concluido. 1 reintentos ejecutados.')
            ->assertSuccessful();

        $sub->refresh();
        $this->assertEquals(2, $sub->failed_attempts_count);
    }
}
