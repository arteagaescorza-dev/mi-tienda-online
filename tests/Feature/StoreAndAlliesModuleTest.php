<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyClearingSettlement;
use App\Models\AllyCouponDirB;
use App\Models\AllyLeadCapture;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AlliesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StoreAndAlliesModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $portalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->portalUser = User::factory()->create(['role' => 'portal']);
        $this->seed(AlliesSeeder::class);
    }

    public function test_store_cms_renders_successfully_with_multiverse_vitrinas(): void
    {
        Product::create([
            'sku' => 'TEST-MARTE-01',
            'title' => 'Arnés Marte Cuero Black',
            'slug' => 'arnes-marte-cuero-black',
            'price' => 790.00,
            'visible' => true,
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get('/admin/tienda-cms');

        $response->assertOk()
            ->assertSee('Marte')
            ->assertSee('Venus')
            ->assertSee('Tiresias')
            ->assertSee('Terra')
            ->assertSee('Editor Visual Multiverso')
            ->assertSee('Simulador de Elasticidad')
            ->assertSee('Arnés Marte Cuero Black');
    }

    public function test_store_shipping_rules_can_be_updated(): void
    {
        $payload = [
            'freeShippingThreshold' => 1599.00,
            'flatRate' => 149.00,
            'localPickupEnabled' => 1,
            'localPickupLocation' => 'Metro Chabacano CDMX L2/L9',
        ];

        $response = $this->from('/admin/tienda-cms?tab=shipping')
            ->actingAs($this->portalUser)
            ->post('/admin/tienda-cms/fletes', $payload);

        $response->assertRedirect('/admin/tienda-cms?tab=shipping')
            ->assertSessionHas('success');

        $cached = Cache::get('cms_shipping_rule');
        $this->assertNotNull($cached);
        $this->assertEquals(1599.00, $cached['freeShippingThreshold']);
        $this->assertEquals(149.00, $cached['flatRate']);
        $this->assertTrue($cached['localPickupEnabled']);
    }

    public function test_store_banner_status_can_be_toggled(): void
    {
        $response = $this->from('/admin/tienda-cms?tab=banners')
            ->actingAs($this->portalUser)
            ->post('/admin/tienda-cms/banners/1/toggle');

        $response->assertRedirect('/admin/tienda-cms?tab=banners')
            ->assertSessionHas('success');

        $banners = Cache::get('cms_banners', []);
        $this->assertNotEmpty($banners);
        $banner1 = collect($banners)->firstWhere('id', 1);
        $this->assertNotNull($banner1);
        $this->assertFalse($banner1['active']); // was true by default, toggled to false
    }

    public function test_store_marquee_status_can_be_toggled(): void
    {
        $response = $this->from('/admin/tienda-cms?tab=banners')
            ->actingAs($this->portalUser)
            ->post('/admin/tienda-cms/marquesina/toggle');

        $response->assertRedirect('/admin/tienda-cms?tab=banners')
            ->assertSessionHas('success');

        $this->assertNotNull(Cache::get('cms_announcement_active'));
    }

    public function test_store_vitrina_layout_can_be_updated(): void
    {
        $response = $this->from('/admin/tienda-cms?universe=marte')
            ->actingAs($this->portalUser)
            ->post('/admin/tienda-cms/vitrinas/marte', [
                'heroTitle' => 'Marte: Nueva Colección Intensa',
                'heroSubtitle' => 'Jocks y arneses de alta densidad',
                'heroCtaText' => 'Explorar Marte',
            ]);

        $response->assertRedirect('/admin/tienda-cms?universe=marte')
            ->assertSessionHas('success');

        $cached = Cache::get('cms_storefront_marte', []);
        $this->assertEquals('Marte: Nueva Colección Intensa', $cached['heroTitle']);
        $this->assertEquals('Explorar Marte', $cached['heroCtaText']);
    }

    public function test_allies_hub_renders_allies_and_clearing_balances(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get('/admin/convenios');

        $response->assertOk()
            ->assertSee('Paradiso Circuit CDMX')
            ->assertSee('Alex Rivera')
            ->assertSee('Sodome Spa &amp; Club CDMX', false)
            ->assertSee('Cámara de Compensación')
            ->assertSee('Ejecutar Liquidación');
    }

    public function test_ticket_voucher_validation_success(): void
    {
        $ally = Ally::where('code', 'ALLY-PARADISO')->firstOrFail();
        $voucher = AllyLeadCapture::create([
            'voucher_code' => 'TEST-VOUCH-99',
            'ally_id' => $ally->id,
            'lead_name' => 'Roberto Gómez',
            'lead_whatsapp' => '+525511229988',
            'lead_email' => 'roberto@test.com',
            'universe' => 'marte',
            'is_redeemed' => false,
            'capi_match_score' => 9.85,
        ]);

        $response = $this->actingAs($this->portalUser)
            ->postJson('/admin/convenios/canje/validar', [
                'voucher_code' => 'TEST-VOUCH-99',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'lead_name' => 'Roberto Gómez',
                'voucher_code' => 'TEST-VOUCH-99',
            ]);

        $voucher->refresh();
        $this->assertTrue($voucher->is_redeemed);
        $this->assertNotNull($voucher->redeemed_at);
    }

    public function test_ticket_voucher_already_redeemed_returns_422(): void
    {
        $ally = Ally::where('code', 'ALLY-PARADISO')->firstOrFail();
        AllyLeadCapture::create([
            'voucher_code' => 'TEST-VOUCH-REDEEMED',
            'ally_id' => $ally->id,
            'lead_name' => 'Mario Bros',
            'lead_whatsapp' => '+525544332211',
            'lead_email' => 'mario@test.com',
            'universe' => 'marte',
            'is_redeemed' => true,
            'redeemed_at' => now(),
            'redeemed_by' => 'Taquilla #1',
            'capi_match_score' => 9.50,
        ]);

        $response = $this->actingAs($this->portalUser)
            ->postJson('/admin/convenios/canje/validar', [
                'voucher_code' => 'TEST-VOUCH-REDEEMED',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'already_redeemed' => true,
            ]);
    }

    public function test_ticket_voucher_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->postJson('/admin/convenios/canje/validar', [
                'voucher_code' => 'NON-EXISTENT-VOUCHER',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_clearing_settlement_execution_creates_settlement_record(): void
    {
        $ally = Ally::where('code', 'ALLY-PARADISO')->firstOrFail();
        $ally->update(['clearing_balance' => -4500.00]);

        $response = $this->from('/admin/convenios?tab=clearing')
            ->actingAs($this->portalUser)
            ->post("/admin/convenios/aliados/{$ally->id}/liquidar", [
                'settlement_type' => 'spei_transfer',
            ]);

        $response->assertRedirect('/admin/convenios?tab=clearing')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ally_clearing_settlements', [
            'ally_id' => $ally->id,
            'settlement_type' => 'spei_transfer',
            'status' => 'executed',
        ]);

        $ally->refresh();
        $this->assertEquals(0.00, (float)$ally->clearing_balance);
    }

    public function test_export_door_list_csv_downloads_masked_file(): void
    {
        $ally = Ally::where('code', 'ALLY-PARADISO')->firstOrFail();

        $response = $this->actingAs($this->portalUser)
            ->get("/admin/convenios/aliados/{$ally->id}/export-csv");

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Folio Voucher', $content);
        $this->assertStringContainsString('Nombre Asistente', $content);
        $this->assertStringContainsString('WhatsApp Enmascarado', $content);
        $this->assertStringContainsString('****', $content); // Phone masking
    }

    public function test_tokenized_partner_portal_extranet_renders_for_ally(): void
    {
        $ally = Ally::where('code', 'ALLY-PARADISO')->firstOrFail();

        $response = $this->actingAs($this->portalUser)
            ->get("/portal/aliado/{$ally->portal_token}");

        $response->assertOk()
            ->assertSee($ally->name)
            ->assertSee($ally->portal_token)
            ->assertSee('Descargar CSV Taquilla')
            ->assertSee('Validador de Vouchers en Puerta');
    }
}
