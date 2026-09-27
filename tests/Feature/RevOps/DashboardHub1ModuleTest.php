<?php

namespace Tests\Feature\RevOps;

use App\Models\User;
use App\Models\Order;
use App\Models\Lead;
use App\Models\Cart;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardHub1ModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_dashboard_renders_with_triple_rbac_and_hides_financials_for_warehouse_role(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin.revops@mitienda.online',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-2026-TEST-001',
            'user_id' => $adminUser->id,
            'total' => 1850.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'shipping_address' => 'Av. Insurgentes Sur 1200, Benito Juarez, CDMX',
        ]);

        Lead::create([
            'name' => 'Roberto Garza',
            'email' => 'roberto@example.com',
            'status' => 'new',
            'metadata' => ['score' => 88],
        ]);

        Cart::create([
            'status' => 'abandoned',
            'guest_token' => 'guest-cart-token-001',
        ]);

        $supplier = Supplier::create([
            'name' => 'Torzo Test',
            'contact_email' => 'torzo@test.com',
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-2026-TEST-01',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'total_units' => 40,
            'subtotal' => 4400.00,
            'tax' => 704.00,
            'total_amount' => 5104.00,
        ]);

        // 1. Verificacion para Super Admin / Direccion
        $responseAdmin = $this->actingAs($adminUser)->get('/admin/dashboard');

        $responseAdmin->assertOk()
            ->assertSee('window.serverDashboardData', false)
            ->assertSee('Dashboard Ejecutivo')
            ->assertSee('Bandeja Operativa')
            ->assertSee('Atención & Ventas', false)
            ->assertSee('Feed RevOps: En Vivo')
            ->assertSee('Ventas hoy');

        // 2. Verificacion para Rol de Almacen (Politica estricta de privacidad financiera)
        $responseWarehouse = $this->actingAs($adminUser)->get('/admin/dashboard?role=warehouse');

        $responseWarehouse->assertOk()
            ->assertSee('window.serverDashboardData', false)
            ->assertSee('Modo Almacén & Empaque Físico (Carlos Almacén)', false)
            ->assertSee('Pedidos por empacar')
            ->assertSee('Recepciones en tránsito')
            ->assertSee('Politica de Privacidad Financiera Activa');

        // 3. Verificacion para Rol de Ventas y Atencion
        $responseSales = $this->actingAs($adminUser)->get('/admin/dashboard?role=sales');

        $responseSales->assertOk()
            ->assertSee('window.serverDashboardData', false)
            ->assertSee('Modo Atención & Ventas Activo (Ana Atención)', false)
            ->assertSee('Leads activos sin responder')
            ->assertSee('Carritos abandonados hoy')
            ->assertSee('Pedidos pausados por dirección');
    }
}
