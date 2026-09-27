<?php

namespace Tests\Feature\Governance;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SystemAuditLog;
use App\Models\ThreeWayMatching;
use App\Models\User;
use App\Services\Governance\AuditTrailService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function createRoleUser(string $role, string $email): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Usuario ' . ucfirst($role),
                'password' => bcrypt('password'),
                'role' => $role,
            ]
        );
    }

    public function test_audit_log_creation_with_attributes_and_diff(): void
    {
        $admin = $this->createRoleUser('admin', 'admin.audit@mitienda.test');
        $service = app(AuditTrailService::class);

        $log = $service->logAction(
            action: 'price_override',
            module: 'inventory',
            referenceCode: 'SKU-TEST-AUDIT',
            oldValues: ['price' => 399.00, 'status' => 'draft'],
            newValues: ['price' => 450.00, 'status' => 'published'],
            description: 'Actualizacion de precio de lanzamiento',
            user: $admin
        );

        $this->assertInstanceOf(SystemAuditLog::class, $log);
        $this->assertDatabaseHas('system_audit_logs', [
            'id' => $log->id,
            'action' => 'price_override',
            'module' => 'inventory',
            'reference_code' => 'SKU-TEST-AUDIT',
            'user_name' => $admin->name,
        ]);

        $diff = $log->diff_summary;
        $this->assertEquals(399.00, $diff['price']['old']);
        $this->assertEquals(450.00, $diff['price']['new']);
        $this->assertEquals('draft', $diff['status']['old']);
        $this->assertEquals('published', $diff['status']['new']);
    }

    public function test_audit_log_inmutability_blocks_updating_and_deleting(): void
    {
        $service = app(AuditTrailService::class);
        $log = $service->logAction(
            action: 'critical_adjustment',
            module: 'finance',
            referenceCode: 'REF-IMMUTABLE-01',
            oldValues: ['balance' => 1000],
            newValues: ['balance' => 500],
            description: 'Ajuste inmutable'
        );

        // Intento de modificar el registro debe ser bloqueado con DomainException
        try {
            $log->update(['description' => 'Intento de alteracion maliciosa']);
            $this->fail('Se esperaba DomainException al intentar actualizar un log de auditoria.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }

        // Intento de borrar el registro debe ser bloqueado con DomainException
        try {
            $log->delete();
            $this->fail('Se esperaba DomainException al intentar eliminar un log de auditoria.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }

        // El registro sigue existiendo intacto
        $this->assertDatabaseHas('system_audit_logs', [
            'id' => $log->id,
            'description' => 'Ajuste inmutable',
        ]);
    }

    public function test_domain_helpers_log_price_change_stock_adjustment_and_spei_unblock(): void
    {
        $admin = $this->createRoleUser('superadmin', 'super.audit@mitienda.test');
        $service = app(AuditTrailService::class);

        // 1. Cambio de precio
        $product = Product::create([
            'sku' => 'PROD-AUDIT-SKU',
            'title' => 'Boxer Microfibra Velvet',
            'slug' => 'boxer-microfibra-velvet-audit',
            'price' => 299.00,
            'cost' => 100.00,
        ]);

        $logPrice = $service->logPriceChange($product, 299.00, 349.00, 'Aumento de costo de elastico', $admin);
        $this->assertEquals('price_change', $logPrice->action);
        $this->assertEquals('PROD-AUDIT-SKU', $logPrice->reference_code);

        // 2. Ajuste manual de stock
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'VAR-AUDIT-01',
            'price' => 349.00,
            'cost' => 100.00,
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'pasillo-c-rack-2',
            'quantity' => 50,
            'reserved' => 0,
        ]);

        $logStock = $service->logStockAdjustment($inventory, 50, 42, 'Piezas danadas por humedad', $admin);
        $this->assertEquals('manual_stock_adjustment', $logStock->action);
        $this->assertEquals('VAR-AUDIT-01', $logStock->reference_code);
        $this->assertEquals(50, $logStock->old_values['quantity']);
        $this->assertEquals(42, $logStock->new_values['quantity']);

        // 3. Desbloqueo SPEI
        $threeWayService = app(\App\Services\Finance\ThreeWayMatchingService::class);
        $matching = $threeWayService->evaluateInboundMatch(
            poCode: 'OC-AUDIT-99',
            supplierId: 'VND-01',
            supplierName: 'Proveedor Textil',
            poQty: 100,
            poTotal: 15000.00,
            whReceivedQty: 90,
            whDamagedQty: 0,
            invoiceNumber: 'FAC-99',
            invoiceQty: 100,
            invoiceGross: 15000.00
        );

        $logSpei = $service->logSpeiUnblock($matching, 'SPEI-AUTH-2026-OK', $admin);
        $this->assertEquals('spei_unblock', $logSpei->action);
        $this->assertEquals('OC-AUDIT-99', $logSpei->reference_code);
        $this->assertEquals(0, $logSpei->new_values['spei_payout_blocked']);
    }

    public function test_role_switch_automatically_creates_audit_log(): void
    {
        $admin = $this->createRoleUser('admin', 'admin.logger@mitienda.test');
        $this->actingAs($admin);

        $response = $this->postJson(route('portal.roles.switch'), ['role' => 'warehouse']);
        $response->assertStatus(200);

        // Verificar que switchRole creo un log de auditoria
        $this->assertDatabaseHas('system_audit_logs', [
            'action' => 'role_switch',
            'module' => 'governance',
            'reference_code' => 'admin.logger@mitienda.test',
        ]);
    }

    public function test_audit_logs_api_requires_admin_authorization(): void
    {
        // 1. Rol restringido (warehouse) no puede acceder
        $warehouse = $this->createRoleUser('warehouse', 'almacen.noaudit@mitienda.test');
        $this->actingAs($warehouse);

        $resDenied = $this->getJson('/api/v1/governance/audit-logs');
        $resDenied->assertStatus(403);
        $resDenied->assertJsonFragment([
            'error' => 'Acceso denegado por politica de gobernanza y auditoria RBAC (TASK-DSH-09).',
        ]);

        // 2. Administrador si puede consultar
        $admin = $this->createRoleUser('admin', 'admin.yesaudit@mitienda.test');
        $this->actingAs($admin);

        $resAllowed = $this->getJson('/api/v1/governance/audit-logs');
        $resAllowed->assertStatus(200);
        $resAllowed->assertJsonStructure([
            'success',
            'stats' => [
                'total_events',
                'events_today',
                'price_changes_count',
                'stock_adjustments_count',
            ],
            'data',
            'pagination' => [
                'current_page',
                'total',
            ],
        ]);
    }

    public function test_audit_stats_aggregations(): void
    {
        $service = app(AuditTrailService::class);

        $service->logAction('price_change', 'inventory', 'SKU-1', ['p' => 10], ['p' => 20], 'Ajuste 1');
        $service->logAction('price_change', 'inventory', 'SKU-2', ['p' => 30], ['p' => 40], 'Ajuste 2');
        $service->logAction('manual_stock_adjustment', 'inventory', 'SKU-3', ['q' => 10], ['q' => 15], 'Conteo fisico');

        $stats = $service->getAuditStats();

        $this->assertGreaterThanOrEqual(2, $stats['price_changes_count']);
        $this->assertGreaterThanOrEqual(1, $stats['stock_adjustments_count']);
        $this->assertGreaterThanOrEqual(3, $stats['total_events']);
    }
}
