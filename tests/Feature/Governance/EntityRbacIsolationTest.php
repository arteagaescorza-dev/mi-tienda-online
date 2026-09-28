<?php

namespace Tests\Feature\Governance;

use App\Models\Ally;
use App\Models\Supplier;
use App\Models\SystemAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EntityRbacIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Rutas de prueba para verificar aislamiento de entidades via middleware
        Route::middleware(['web', 'auth', 'entity.isolation:vendor'])->get('/test/vendor/protected', function () {
            return response()->json([
                'success' => true,
                'current_supplier_id' => request()->attributes->get('current_supplier_id'),
            ]);
        });

        Route::middleware(['web', 'auth', 'entity.isolation:ally'])->get('/test/ally/protected', function () {
            return response()->json([
                'success' => true,
                'current_ally_id' => request()->attributes->get('current_ally_id'),
            ]);
        });
    }

    /**
     * Verifica que el modelo User relacione correctamente con Supplier y Ally.
     */
    public function test_user_model_has_supplier_and_ally_relations(): void
    {
        $supplier = Supplier::create([
            'name' => 'Textiles Mexicanos SA de CV',
            'contact_name' => 'Alberto Gomez',
            'email' => 'alberto@textilesmx.com',
            'phone' => '5511223344',
            'is_active' => true,
        ]);

        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $supplier->id,
        ]);

        $this->assertTrue($vendorUser->isVendor());
        $this->assertFalse($vendorUser->isAlly());
        $this->assertEquals($supplier->id, $vendorUser->supplier->id);

        $ally = Ally::create([
            'code' => 'ALLY-TEST-01',
            'name' => 'Club Deportivo Aliado',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'portal_token' => 'tok_test_ally_123',
            'status' => 'active',
        ]);

        $allyUser = User::factory()->create([
            'role' => 'ally',
            'ally_id' => $ally->id,
        ]);

        $this->assertTrue($allyUser->isAlly());
        $this->assertFalse($allyUser->isVendor());
        $this->assertEquals($ally->id, $allyUser->ally->id);
    }

    /**
     * Verifica que ni vendor ni ally puedan acceder a datos financieros sensibles de la tienda.
     */
    public function test_vendor_and_ally_cannot_access_store_financials(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fabrica Confecciones',
            'contact_name' => 'Carlos Perez',
            'email' => 'carlos@confecciones.com',
        ]);

        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $supplier->id,
        ]);

        $this->assertFalse($vendorUser->canAccessFinancialData());

        $ally = Ally::create([
            'code' => 'ALLY-TEST-02',
            'name' => 'Aliado Estetica',
            'category' => 'beauty_spa',
            'universe' => 'venus',
            'portal_token' => 'tok_test_ally_456',
        ]);

        $allyUser = User::factory()->create([
            'role' => 'ally',
            'ally_id' => $ally->id,
        ]);

        $this->assertFalse($allyUser->canAccessFinancialData());
    }

    /**
     * Verifica que un vendor unicamente pueda acceder al hub vendor_extranet y a ningun otro hub de la tienda.
     */
    public function test_vendor_can_only_access_vendor_extranet_hub(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fabrica Central',
            'contact_name' => 'Elena Rios',
            'email' => 'elena@fabricacentral.com',
        ]);

        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $supplier->id,
        ]);

        $this->assertTrue($vendorUser->canAccessHub('vendor_extranet'));
        $this->assertFalse($vendorUser->canAccessHub('inventory'));
        $this->assertFalse($vendorUser->canAccessHub('dashboard'));
        $this->assertFalse($vendorUser->canAccessHub('finance'));
        $this->assertFalse($vendorUser->canAccessHub('logistics'));
        $this->assertFalse($vendorUser->canAccessHub('store'));
        $this->assertFalse($vendorUser->canAccessHub('crm'));
        $this->assertFalse($vendorUser->canAccessHub('allies'));
    }

    /**
     * Verifica que un ally unicamente pueda acceder al hub ally_extranet y a ningun otro hub de la tienda.
     */
    public function test_ally_can_only_access_ally_extranet_hub(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-TEST-03',
            'name' => 'Discoteca Aliada',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'portal_token' => 'tok_test_ally_789',
        ]);

        $allyUser = User::factory()->create([
            'role' => 'ally',
            'ally_id' => $ally->id,
        ]);

        $this->assertTrue($allyUser->canAccessHub('ally_extranet'));
        $this->assertFalse($allyUser->canAccessHub('allies'));
        $this->assertFalse($allyUser->canAccessHub('dashboard'));
        $this->assertFalse($allyUser->canAccessHub('finance'));
        $this->assertFalse($allyUser->canAccessHub('inventory'));
        $this->assertFalse($allyUser->canAccessHub('logistics'));
    }

    /**
     * Verifica que switchRole permita cambiar hacia roles vendor y ally registrando auditoria.
     */
    public function test_switch_role_allows_vendor_and_ally_with_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Conmutar a vendor
        $responseVendor = $this->postJson('/admin/roles/switch', ['role' => 'vendor']);
        $responseVendor->assertStatus(200);
        $responseVendor->assertJson([
            'success' => true,
            'active_role' => 'vendor',
            'can_access_financials' => false,
        ]);

        $this->assertDatabaseHas('system_audit_logs', [
            'action' => 'role_switch',
            'user_id' => $admin->id,
        ]);

        // Conmutar a ally
        $responseAlly = $this->postJson('/admin/roles/switch', ['role' => 'ally']);
        $responseAlly->assertStatus(200);
        $responseAlly->assertJson([
            'success' => true,
            'active_role' => 'ally',
            'can_access_financials' => false,
        ]);
    }

    /**
     * Verifica que el middleware de aislamiento bloquee a un vendor sin supplier_id asignado.
     */
    public function test_ensure_entity_isolation_blocks_vendor_without_supplier_id(): void
    {
        $orphanVendor = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => null,
        ]);

        $this->actingAs($orphanVendor);

        $response = $this->get('/test/vendor/protected');
        $response->assertStatus(403);
    }

    /**
     * Verifica que el middleware de aislamiento permita el acceso a un vendor con supplier_id valido.
     */
    public function test_ensure_entity_isolation_allows_authorized_vendor(): void
    {
        $supplier = Supplier::create([
            'name' => 'Industrias Textiles del Norte',
            'contact_name' => 'Luis Garza',
            'email' => 'luis@textilesnorte.com',
        ]);

        $validVendor = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $supplier->id,
        ]);

        $this->actingAs($validVendor);

        $response = $this->get('/test/vendor/protected');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'current_supplier_id' => $supplier->id,
        ]);
    }

    /**
     * Verifica que el middleware de aislamiento bloquee a un ally sin ally_id asignado.
     */
    public function test_ensure_entity_isolation_blocks_ally_without_ally_id(): void
    {
        $orphanAlly = User::factory()->create([
            'role' => 'ally',
            'ally_id' => null,
        ]);

        $this->actingAs($orphanAlly);

        $response = $this->get('/test/ally/protected');
        $response->assertStatus(403);
    }

    /**
     * Verifica que el middleware de aislamiento permita el acceso a un ally con ally_id valido.
     */
    public function test_ensure_entity_isolation_allows_authorized_ally(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-CONF-01',
            'name' => 'Aliado Confirmado SAS',
            'category' => 'onlyfans_creator',
            'universe' => 'tiresias',
            'portal_token' => 'tok_conf_ally_01',
        ]);

        $validAlly = User::factory()->create([
            'role' => 'ally',
            'ally_id' => $ally->id,
        ]);

        $this->actingAs($validAlly);

        $response = $this->get('/test/ally/protected');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'current_ally_id' => $ally->id,
        ]);
    }

    /**
     * Verifica que un administrador global tenga bypass para acceder a los endpoints protegidos.
     */
    public function test_global_admin_can_bypass_entity_isolation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $responseVendor = $this->get('/test/vendor/protected');
        $responseVendor->assertStatus(200);

        $responseAlly = $this->get('/test/ally/protected');
        $responseAlly->assertStatus(200);
    }

    /**
     * Verifica que usuarios con rol vendor o ally no puedan acceder a pantallas administrativas internas (Hub 1-6, 8).
     */
    public function test_vendor_and_ally_are_forbidden_from_internal_admin_hubs(): void
    {
        $supplier = Supplier::create([
            'name' => 'Textiles El Aguila',
            'contact_name' => 'Rosa Maria',
            'email' => 'rosa@elaguila.com',
        ]);

        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'supplier_id' => $supplier->id,
        ]);

        $this->actingAs($vendorUser);

        // Intento de acceso a Dashboard (Hub 1)
        $this->get('/admin/dashboard')->assertStatus(403);

        // Intento de acceso a Inventario (Hub 4)
        $this->get('/admin/inventario-catalogo')->assertStatus(403);

        // Intento de acceso a Finanzas (Hub 5)
        $this->get('/admin/finanzas')->assertStatus(403);

        // Intento de acceso a Gobernanza (Hub 8)
        $this->get('/admin/gobernanza')->assertStatus(403);

        $ally = Ally::create([
            'code' => 'ALLY-FORB-01',
            'name' => 'Aliado Bloqueado SA',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'portal_token' => 'tok_forb_01',
        ]);

        $allyUser = User::factory()->create([
            'role' => 'ally',
            'ally_id' => $ally->id,
        ]);

        $this->actingAs($allyUser);

        // Ally tampoco debe acceder a los hubs administrativos internos
        $this->get('/admin/dashboard')->assertStatus(403);
        $this->get('/admin/inventario-catalogo')->assertStatus(403);
        $this->get('/admin/finanzas')->assertStatus(403);
        $this->get('/admin/gobernanza')->assertStatus(403);
    }
}
