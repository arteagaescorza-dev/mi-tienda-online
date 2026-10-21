<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyCouponDirB;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Hub7AlliesAndScrapingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new \Database\Seeders\AlliesSeeder())->run();
    }

    public function test_scraping_policy_enforces_three_day_interval_unless_forced()
    {
        $supplier = Supplier::create([
            'name' => 'Supplier Test Scrape',
            'slug' => 'supplier-test-scrape',
            'type' => 'dropship_scrape',
            'website' => 'https://mock.supplier.test/',
            'is_active' => true,
            'scrape_frequency' => 'every_3_days',
        ]);

        \App\Models\SupplierSyncRun::create([
            'supplier_id' => $supplier->id,
            'status' => 'success',
            'started_at' => now()->subDay(),
            'finished_at' => now()->subDay(),
        ]);

        // Run sync without --force -> should skip because last run was 1 day ago (policy: 3 days)
        $this->artisan('supplier:scrape', ['supplier_id' => $supplier->id])
            ->expectsOutputToContain('permite 1 consulta cada 3')
            ->assertExitCode(0);

        // Run sync with --force -> should proceed and not be skipped by the 3-day restriction
        $this->artisan('supplier:scrape', ['supplier_id' => $supplier->id, '--force' => true])
            ->assertExitCode(0);
    }

    public function test_superadmin_can_create_new_ally_with_commercial_agreements_and_discount_policies()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'superadmin@mitienda.test',
        ]);

        $payload = [
            'name' => 'Sauna Termas Roma VIP',
            'code' => 'AL-TERMAS-ROMA',
            'category' => 'sauna',
            'universe' => 'marte',
            'contact_name' => 'Marco Aurelio',
            'contact_whatsapp' => '+52 55 9988 7766',
            'contact_email' => 'gerencia@termasroma.com',
            'social_handle' => '@termasromamx',
            'lead_acquisition_cost' => 35.00,
            'commission_type' => 'percentage',
            'commission_rate' => 15.00,
            'max_discount_pct' => 25.00,
            'allowed_discount_types' => ['cover', 'free_drink', 'store_discount'],
            'bank_name' => 'BBVA Bancomer',
            'bank_clabe' => '012180001234567890',
            'status' => 'active',
            'notes' => 'Acuerdo firmado de intercambio de audiencias en vitrina Marte.',
        ];

        $response = $this->actingAs($superadmin)->post(route('portal.allies.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('allies', [
            'code' => 'AL-TERMAS-ROMA',
            'name' => 'Sauna Termas Roma VIP',
            'category' => 'sauna',
            'universe' => 'marte',
            'lead_acquisition_cost' => 35.00,
            'commission_rate' => 15.00,
            'max_discount_pct' => 25.00,
        ]);

        $newAlly = Ally::where('code', 'AL-TERMAS-ROMA')->first();
        $this->assertNotNull($newAlly->portal_token);
        $this->assertEquals(['cover', 'free_drink', 'store_discount'], $newAlly->allowed_discount_types);
    }

    public function test_non_authorized_user_cannot_create_or_modify_allies()
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'cliente@mitienda.test',
        ]);

        $payload = [
            'name' => 'Aliado No Autorizado',
            'category' => 'spa',
            'universe' => 'venus',
        ];

        $response = $this->actingAs($customer)->post(route('portal.allies.store'), $payload);
        $response->assertStatus(403);
    }

    public function test_superadmin_can_update_ally_agreements_via_put()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $ally = Ally::first();

        $payload = [
            'name' => $ally->name . ' Actualizado',
            'category' => 'party_producer',
            'universe' => 'marte',
            'lead_acquisition_cost' => 45.00,
            'commission_type' => 'percentage',
            'commission_rate' => 18.00,
            'max_discount_pct' => 30.00,
            'allowed_discount_types' => ['cover', 'vip_access'],
        ];

        $response = $this->actingAs($superadmin)->put(route('portal.allies.update', $ally->id), $payload);

        $response->assertRedirect();
        $ally->refresh();
        $this->assertEquals($ally->name, $payload['name']);
        $this->assertEquals(45.00, (float)$ally->lead_acquisition_cost);
        $this->assertEquals(18.00, (float)$ally->commission_rate);
        $this->assertEquals(30.00, (float)$ally->max_discount_pct);
        $this->assertEquals(['cover', 'vip_access'], $ally->allowed_discount_types);
    }

    public function test_partner_can_only_request_event_creation_in_pending_approval_status()
    {
        Storage::fake('public');
        $ally = Ally::first();

        $token = $ally->portal_token;
        $file = UploadedFile::fake()->image('poster_evento.jpg', 600, 800);

        $payload = [
            'title' => 'Festival Noche Estelar 2026',
            'benefit' => '2x1 en Cover antes de las 11 PM',
            'discount_type' => 'cover_2x1',
            'instructions' => 'Presentar QR en taquilla digital',
            'universe' => 'marte',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(1)->toDateString(),
            'banner_image' => $file,
        ];

        $response = $this->post(route('portal.ally.extranet.campaign.submit', $token), $payload);

        $response->assertRedirect();

        // Must be in pending_approval status, NEVER automatically active
        $this->assertDatabaseHas('ally_campaigns_dir_a', [
            'ally_id' => $ally->id,
            'title' => 'Festival Noche Estelar 2026',
            'status' => 'pending_approval',
            'discount_type' => 'cover_2x1',
        ]);

        // It should NOT be visible in active Storefront convenios query
        $activeCampaigns = AllyCampaignDirA::where('status', 'active')
            ->where('title', 'Festival Noche Estelar 2026')
            ->count();
        $this->assertEquals(0, $activeCampaigns);
    }

    public function test_superadmin_can_approve_campaign_and_syncs_automatically_to_storefront()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $ally = Ally::first();
        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => 'Pool Party Exclusiva',
            'slug' => 'pool-party-exclusiva',
            'universe' => 'marte',
            'benefit' => 'Cóctel gratis al llegar',
            'discount_type' => 'free_drink',
            'status' => 'pending_approval',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);

        $response = $this->actingAs($superadmin)->post(route('portal.allies.campaign.approve', $campaign->id), [
            'benefit' => 'Cóctel de cortesía y pulsera preferente',
        ]);

        $response->assertRedirect();

        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);
        $this->assertEquals('Cóctel de cortesía y pulsera preferente', $campaign->benefit);

        // Immediate automatic query check: active in Storefront
        $storefrontActive = AllyCampaignDirA::with('ally')
            ->where('status', 'active')
            ->where('id', $campaign->id)
            ->first();

        $this->assertNotNull($storefrontActive);
        $this->assertEquals('Pool Party Exclusiva', $storefrontActive->title);
    }

    public function test_superadmin_can_reject_campaign_with_reason()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $ally = Ally::first();
        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => 'Evento No Alineado',
            'slug' => 'evento-no-alineado',
            'universe' => 'marte',
            'benefit' => 'Promoción inválida',
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($superadmin)->post(route('portal.allies.campaign.reject', $campaign->id), [
            'reason' => 'El descuento excede las políticas comerciales de la tienda.',
        ]);

        $response->assertRedirect();

        $campaign->refresh();
        $this->assertEquals('rejected', $campaign->status);
        $this->assertEquals('El descuento excede las políticas comerciales de la tienda.', $campaign->rejection_reason);
    }

    public function test_superadmin_can_archive_and_reactivate_ally_and_pauses_campaigns()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $ally = Ally::first();

        // Crear una campaña activa y un cupón activo asociados
        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => 'Campaña Para Archivo',
            'slug' => 'campana-para-archivo',
            'universe' => 'marte',
            'benefit' => 'Descuento 15%',
            'status' => 'active',
        ]);

        $coupon = AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'ARCHIVE15',
            'discount_pct' => 15.00,
            'commission_rate' => 10.00,
            'universe' => 'marte',
            'utm_source' => 'test',
            'utm_campaign' => 'test_camp',
            'status' => 'active',
        ]);

        // 1. Archivar aliado
        $response = $this->actingAs($superadmin)->post(route('portal.allies.archive', $ally->id));
        $response->assertRedirect();

        $ally->refresh();
        $campaign->refresh();
        $coupon->refresh();

        $this->assertEquals('archived', $ally->status);
        $this->assertTrue($ally->isArchived());
        $this->assertEquals('paused', $campaign->status);
        $this->assertEquals('paused', $coupon->status);

        // 2. Reactivar aliado
        $response2 = $this->actingAs($superadmin)->post(route('portal.allies.archive', $ally->id));
        $response2->assertRedirect();

        $ally->refresh();
        $coupon->refresh();

        $this->assertEquals('active', $ally->status);
        $this->assertFalse($ally->isArchived());
        $this->assertEquals('active', $coupon->status);
    }

    public function test_superadmin_can_delete_and_restore_ally_with_soft_deletes()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $ally = Ally::first();
        $allyId = $ally->id;

        // 1. Eliminar (soft delete)
        $response = $this->actingAs($superadmin)->delete(route('portal.allies.destroy', $allyId));
        $response->assertRedirect();

        $this->assertSoftDeleted('allies', ['id' => $allyId]);
        $this->assertNull(Ally::find($allyId));
        $this->assertNotNull(Ally::withTrashed()->find($allyId));

        // 2. Restaurar
        $responseRestore = $this->actingAs($superadmin)->post(route('portal.allies.restore', $allyId));
        $responseRestore->assertRedirect();

        $restoredAlly = Ally::find($allyId);
        $this->assertNotNull($restoredAlly);
        $this->assertEquals('active', $restoredAlly->status);
    }

    public function test_unauthorized_user_cannot_archive_or_delete_ally()
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $ally = Ally::first();

        $resArchive = $this->actingAs($customer)->post(route('portal.allies.archive', $ally->id));
        $resArchive->assertStatus(403);

        $resDelete = $this->actingAs($customer)->delete(route('portal.allies.destroy', $ally->id));
        $resDelete->assertStatus(403);
    }

    public function test_allies_hub_view_renders_clean_subnav_without_duplicate_mode_switcher()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)->get(route('portal.allies'));
        $response->assertOk();

        // Comprobar que NO existe la barra duplicada superior 'allies-mode-switcher'
        $response->assertDontSee('allies-mode-switcher', false);

        // Comprobar que la barra de submódulos 'allies-subnav-bar' está presente como navegación única
        $response->assertSee('allies-subnav-bar', false);
        $response->assertSee('1. Directorio &amp; Acuerdos', false);
        $response->assertSee('2. Solicitudes de Eventos &amp; Convenios', false);
        $response->assertSee('6. Previsualizar Partner Portal', false);
        $response->assertSee('7. Extranet Proveedores B2B', false);

        // Comprobar que los botones de Archivar y Eliminar están disponibles en la vista
        $response->assertSee('Archivar', false);
        $response->assertSee('Eliminar', false);
    }

    public function test_superadmin_can_delete_all_allies_and_view_renders_clean_empty_state_without_404_or_reseeding()
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        // Delete all allies one by one (simulating user action)
        $allies = Ally::all();
        $this->assertGreaterThan(0, $allies->count());

        foreach ($allies as $ally) {
            $response = $this->actingAs($superadmin)->delete(route('portal.allies.destroy', $ally->id));
            $response->assertRedirect(route('portal.allies'));
        }

        // Verify all allies are now soft-deleted (count of non-deleted is 0)
        $this->assertEquals(0, Ally::count());
        $this->assertGreaterThan(0, Ally::withTrashed()->count());

        // Visiting /admin/convenios must return 200 OK without 404, without 500, and without auto-reseeding
        $viewResponse = $this->actingAs($superadmin)->get(route('portal.allies'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Directorio de Aliados Vacío', false);
        $viewResponse->assertSee('+ Alta de Aliado', false);
        $viewResponse->assertSee('0 Aliados Activos', false);

        // Verify that visiting the page did NOT reseed demo allies
        $this->assertEquals(0, Ally::count());

        // Now superadmin creates their own custom ally
        $newPayload = [
            'name' => 'Mi Propio Aliado 2026',
            'code' => 'AL-PROPIO',
            'category' => 'general',
            'universe' => 'marte',
            'lead_acquisition_cost' => 30.00,
            'commission_type' => 'percentage',
            'commission_rate' => 10.00,
            'max_discount_pct' => 15.00,
            'allowed_discount_types' => ['cover'],
            'status' => 'active',
        ];

        $createResponse = $this->actingAs($superadmin)->post(route('portal.allies.store'), $newPayload);
        $createResponse->assertRedirect();

        // The new custom ally exists and is rendered
        $this->assertEquals(1, Ally::count());
        $customView = $this->actingAs($superadmin)->get(route('portal.allies'));
        $customView->assertOk();
        $customView->assertSee('Mi Propio Aliado 2026', false);
        $customView->assertDontSee('Directorio de Aliados Vacío', false);
    }
}
