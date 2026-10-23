<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyLeadCapture;
use App\Models\CmsSection;
use App\Models\Coupon;
use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\User;
use Database\Seeders\AlliesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Hub2PopUpSubscriptionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $portalUser;
    protected Ally $ally;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AlliesSeeder::class);
        $this->portalUser = User::factory()->create(['role' => 'portal']);
        $this->ally = Ally::first();
    }

    public function test_ally_can_submit_event_campaign_with_poster_for_approval(): void
    {
        Storage::fake('public');
        $posterFile = UploadedFile::fake()->image('festival_poster.jpg', 800, 1200);

        $response = $this->post(route('portal.ally.extranet.campaign.submit', ['token' => $this->ally->portal_token]), [
            'title' => 'Festival Orgullo 2026 - Preventa VIP',
            'benefit' => '25% OFF en Taquilla y Bebida de Cortesía',
            'instructions' => 'Presentar código QR en taquilla',
            'display_format' => 'popup_modal',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'poster_file' => $posterFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $campaign = AllyCampaignDirA::where('title', 'Festival Orgullo 2026 - Preventa VIP')->first();
        $this->assertNotNull($campaign);
        $this->assertEquals('pending_approval', $campaign->status);
        $this->assertEquals($this->ally->id, $campaign->ally_id);
        $this->assertNotNull($campaign->banner_image);
    }

    public function test_marketing_can_approve_submitted_ally_campaign(): void
    {
        $campaign = AllyCampaignDirA::create([
            'ally_id' => $this->ally->id,
            'title' => 'Spa Bienestar Gay CDMX',
            'slug' => 'spa-bienestar-gay-cdmx-' . uniqid(),
            'benefit' => '15% de Descuento en Circuito Termal',
            'status' => 'pending_approval',
            'display_format' => 'popup_modal',
            'banner_image' => 'uploads/allies_posters/test_poster.jpg',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.allies.campaign.approve', ['id' => $campaign->id]), [
                'benefit' => '20% de Descuento en Circuito Termal (Acuerdo RevOps)',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);
        $this->assertEquals('20% de Descuento en Circuito Termal (Acuerdo RevOps)', $campaign->benefit);
    }

    public function test_popup_subscription_with_ally_discount_creates_lead_voucher_and_buyer_account(): void
    {
        $campaign = AllyCampaignDirA::create([
            'ally_id' => $this->ally->id,
            'title' => 'Noche de Gala Paradiso',
            'slug' => 'noche-de-gala-paradiso-' . uniqid(),
            'benefit' => 'Bebida Doble + Acceso Fast-Pass',
            'status' => 'active',
            'display_format' => 'popup_modal',
            'leads_count' => 0,
        ]);

        $payload = [
            'popup_action' => 'ally_discount',
            'ally_campaign_id' => $campaign->id,
            'name' => 'Carlos Mendoza',
            'whatsapp' => '5512345678',
            'email' => 'carlos.mendoza@example.com',
            'universe' => 'marte',
        ];

        $response = $this->postJson('/api/v1/marketing/popup/subscribe', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('popup_action', 'ally_discount');

        $data = $response->json();
        $this->assertNotEmpty($data['voucher_code']);

        // Check Hub 3 Lead
        $lead = Lead::where('phone', '5512345678')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('Carlos Mendoza', $lead->name);
        $this->assertEquals('prospect', $lead->lifecycle_stage);
        $this->assertEquals(75, $lead->lead_score);

        // Check Meta CAPI interaction
        $interaction = LeadInteraction::where('lead_id', $lead->id)
            ->where('interaction_type', 'meta_capi_lead_captured')
            ->first();
        $this->assertNotNull($interaction);
        $this->assertEquals('meta_capi_lead_captured', $interaction->interaction_type);
        $this->assertEquals(9.8, (float)($interaction->metadata['capi_score'] ?? 0));

        // Check Buyer User creation
        $user = User::where('phone', '5512345678')->first();
        $this->assertNotNull($user);
        $this->assertEquals('buyer', $user->role);
        $this->assertTrue((bool)$user->two_factor_confirmed);

        // Check Ally Voucher
        $voucher = AllyLeadCapture::where('voucher_code', $data['voucher_code'])->first();
        $this->assertNotNull($voucher);
        $this->assertEquals($campaign->id, $voucher->campaign_id);
        $this->assertEquals($this->ally->id, $voucher->ally_id);
        $this->assertFalse((bool)$voucher->is_redeemed);

        // Check campaign counter incremented
        $campaign->refresh();
        $this->assertEquals(1, $campaign->leads_count);
    }

    public function test_popup_subscription_with_store_discount_generates_single_use_coupon(): void
    {
        $payload = [
            'popup_action' => 'store_discount',
            'store_discount_value' => 20,
            'store_discount_type' => 'percent',
            'min_order' => 499,
            'days_valid' => 15,
            'name' => 'Valeria Ruiz',
            'whatsapp' => '5587654321',
            'email' => 'valeria.ruiz@example.com',
            'universe' => 'venus',
        ];

        $response = $this->postJson('/api/v1/marketing/popup/subscribe', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('popup_action', 'store_discount');

        $data = $response->json();
        $this->assertNotEmpty($data['voucher_code']);

        // Check Coupon in database
        $coupon = Coupon::where('code', $data['voucher_code'])->first();
        $this->assertNotNull($coupon);
        $this->assertEquals(1, $coupon->max_uses);
        $this->assertEquals(0, $coupon->used_count);
        $this->assertEquals(20.0, (float)$coupon->discount_amount);
    }

    public function test_popup_subscription_with_presale_redirects_to_product(): void
    {
        $payload = [
            'popup_action' => 'presale',
            'presale_url' => '/producto/binder-pro-tiresias-v2',
            'name' => 'Alex Rivera',
            'whatsapp' => '5599887766',
            'email' => 'alex.rivera@example.com',
            'universe' => 'tiresias',
        ];

        $response = $this->postJson('/api/v1/marketing/popup/subscribe', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('popup_action', 'presale')
            ->assertJsonPath('redirect_url', '/producto/binder-pro-tiresias-v2');

        // Check lead created
        $lead = Lead::where('phone', '5599887766')->first();
        $this->assertNotNull($lead);
    }

    public function test_popup_subscription_with_newsletter_does_not_generate_coupon(): void
    {
        $payload = [
            'popup_action' => 'newsletter',
            'name' => 'Sofia Lopez',
            'whatsapp' => '5544332211',
            'email' => 'sofia.lopez@example.com',
            'universe' => 'marte',
        ];

        $response = $this->postJson('/api/v1/marketing/popup/subscribe', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('popup_action', 'newsletter')
            ->assertJsonPath('voucher_code', null);

        $lead = Lead::where('phone', '5544332211')->first();
        $this->assertNotNull($lead);
    }

    public function test_customer_discounts_endpoint_returns_real_vouchers_for_account(): void
    {
        $campaign = AllyCampaignDirA::create([
            'ally_id' => $this->ally->id,
            'title' => 'Circuito CDMX Especial',
            'slug' => 'circuito-cdmx-especial-' . uniqid(),
            'benefit' => '20% OFF en Tienda',
            'status' => 'active',
            'display_format' => 'popup_modal',
        ]);

        // Create voucher for this customer
        $voucher = AllyLeadCapture::create([
            'voucher_code' => 'CIRCUITO-TEST-999',
            'campaign_id' => $campaign->id,
            'ally_id' => $this->ally->id,
            'lead_name' => 'Mariana Gomez',
            'lead_whatsapp' => '5533221100',
            'lead_email' => 'mariana.gomez@example.com',
            'universe' => 'marte',
            'is_redeemed' => false,
        ]);

        $response = $this->getJson('/api/v1/store/account/discounts?email=mariana.gomez@example.com&phone=5533221100');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $discounts = $response->json('discounts');
        $this->assertNotEmpty($discounts);
        $this->assertEquals('CIRCUITO-TEST-999', $discounts[0]['code']);
        $this->assertFalse($discounts[0]['redeemed']);
    }

    public function test_hub_2_store_section_creation_with_popup_configuration(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->post('/admin/tienda-cms/secciones', [
                'universe' => 'marte',
                'type' => 'pop_up',
                'name' => 'Pop-up Primavera 2026',
                'title' => 'Obtén tu Beneficio de Primavera',
                'subtitle' => 'Regístrate con tu WhatsApp para obtener acceso preferente.',
                'badge' => 'OFERTA TEMPORAL',
                'status' => 'active',
                'settings' => [
                    'popup_action' => 'store_discount',
                    'popup_store_discount_value' => 20,
                    'popup_store_discount_type' => 'percent',
                    'popup_min_order' => 400,
                    'popup_days_valid' => 30,
                    'popup_enable_gradient' => 1,
                    'popup_gradient_color_start' => '#0f172a',
                    'popup_gradient_color_end' => '#000000',
                    'popup_gradient_direction' => 'to bottom',
                    'popup_gradient_opacity_start' => 85,
                    'popup_gradient_opacity_end' => 95,
                    'popup_image_focal_point' => 'center center',
                    'popup_cta_text' => 'Quiero mi Descuento',
                    'popup_close_behavior' => 'outside',
                    'popup_delay_seconds' => 3,
                    'popup_frequency' => 'once_per_session',
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $section = CmsSection::where('name', 'Pop-up Primavera 2026')->first();
        $this->assertNotNull($section);
        $this->assertEquals('pop_up', $section->type);
        $this->assertEquals('store_discount', $section->settings['popup_action'] ?? null);
        $this->assertEquals('center center', $section->settings['popup_image_focal_point'] ?? null);
    }
}
