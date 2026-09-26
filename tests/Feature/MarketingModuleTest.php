<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Coupon;
use App\Models\EmailAutomation;
use App\Models\ChatbotConversation;
use App\Models\AdCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingModuleTest extends TestCase
{
    use RefreshDatabase;

    private function portalUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'portal.marketing@test.com'],
            [
                'name' => 'Portal Marketing Tester',
                'password' => bcrypt('secret123'),
                'role' => 'portal',
            ]
        );
    }

    public function test_portal_user_can_access_marketing_hub_with_real_database_records(): void
    {
        Lead::create([
            'name' => 'Carlos Mendoza',
            'email' => 'carlos.m@example.com',
            'phone' => '+52 81 1234 5678',
            'status' => 'won',
            'metadata' => [
                'universe' => 'marte',
                'stage' => 'subscriber',
                'score' => 95,
                'ltv' => 3450.00,
                'orders_count' => 4,
            ],
        ]);

        Coupon::create([
            'code' => 'TESTMKT15',
            'discount_type' => 'percent',
            'discount_value' => 15.00,
            'target_universe' => 'marte',
            'is_active' => true,
        ]);

        EmailAutomation::create([
            'slug' => 'test-auto-welcome',
            'title' => 'Bienvenida Multiverso Test',
            'trigger_event' => 'user.registered',
            'delay_minutes' => 0,
            'is_active' => true,
        ]);

        ChatbotConversation::create([
            'channel' => 'whatsapp',
            'session_status' => 'bot_active',
            'customer_name' => 'Rodrigo Test',
            'customer_phone' => '+52 55 1111 2222',
            'messages_json' => [
                ['sender' => 'user', 'text' => 'Hola bot', 'time' => '10:00'],
            ],
        ]);

        AdCampaign::create([
            'platform' => 'meta_ads',
            'campaign_id_external' => 'meta-test-01',
            'name' => 'Meta Ads Boxers Test',
            'universe' => 'marte',
            'daily_budget' => 2000.00,
            'platform_roas' => 4.5,
            'net_real_roas' => 3.2,
            'cpa' => 45.00,
            'ctr' => 4.0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->portalUser())
            ->get(route('portal.marketing'));

        $response->assertOk()
            ->assertSee('Marketing')
            ->assertSee('CRM', false)
            ->assertSee('Carlos Mendoza')
            ->assertSee('TESTMKT15')
            ->assertSee('Bienvenida Multiverso Test')
            ->assertSee('Rodrigo Test')
            ->assertSee('Meta Ads Boxers Test');
    }

    public function test_can_create_a_coupon(): void
    {
        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.marketing.coupons.store'), [
                'code' => 'VERANO20',
                'discount_type' => 'percent',
                'discount_value' => 20.00,
                'min_order_amount' => 499.00,
                'target_universe' => 'marte',
                'max_uses' => 200,
            ]);

        $response->assertRedirect(route('portal.marketing', ['tab' => 'coupons']));
        $this->assertDatabaseHas('coupons', [
            'code' => 'VERANO20',
            'discount_value' => 20.00,
        ]);
    }

    public function test_can_toggle_a_coupon_status(): void
    {
        $coupon = Coupon::create([
            'code' => 'TOGGLE10',
            'discount_type' => 'percent',
            'discount_value' => 10.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.marketing.coupons.toggle', $coupon->id));

        $response->assertRedirect(route('portal.marketing', ['tab' => 'coupons']));
        $coupon->refresh();
        $this->assertFalse($coupon->is_active);
    }

    public function test_can_scale_campaign_daily_budget(): void
    {
        $campaign = AdCampaign::create([
            'platform' => 'meta_ads',
            'campaign_id_external' => 'meta-scale-01',
            'name' => 'Meta Scale Camp',
            'daily_budget' => 1000.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.marketing.campaigns.scale', $campaign->id));

        $response->assertRedirect(route('portal.marketing', ['tab' => 'ads']));
        $campaign->refresh();
        $this->assertEquals(1200.00, (float) $campaign->daily_budget);
    }

    public function test_can_pause_and_resume_campaign(): void
    {
        $campaign = AdCampaign::create([
            'platform' => 'meta_ads',
            'campaign_id_external' => 'meta-pause-01',
            'name' => 'Meta Pause Camp',
            'daily_budget' => 1000.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.marketing.campaigns.pause', $campaign->id));

        $response->assertRedirect(route('portal.marketing', ['tab' => 'ads']));
        $campaign->refresh();
        $this->assertEquals('paused', $campaign->status);
    }

    public function test_can_takeover_chatbot_conversation(): void
    {
        $conversation = ChatbotConversation::create([
            'channel' => 'whatsapp',
            'session_status' => 'bot_active',
            'customer_name' => 'Lucía RMA',
            'messages_json' => [],
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.marketing.conversations.takeover', $conversation->id));

        $response->assertRedirect(route('portal.marketing', ['tab' => 'chatbot']));
        $conversation->refresh();
        $this->assertEquals('human_takeover', $conversation->session_status);
    }

    public function test_can_toggle_email_automation(): void
    {
        $automation = EmailAutomation::create([
            'slug' => 'test-toggle-auto',
            'title' => 'Email Toggle Test',
            'trigger_event' => 'order.delivered',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.marketing.automations.toggle', $automation->id));

        $response->assertRedirect(route('portal.marketing', ['tab' => 'campaigns']));
        $automation->refresh();
        $this->assertFalse($automation->is_active);
    }

    public function test_portal_scoring_renders_real_leads(): void
    {
        Lead::create([
            'name' => 'Top Scor Lead',
            'email' => 'toplead@test.com',
            'status' => 'won',
            'metadata' => ['score' => 98],
        ]);

        $response = $this->actingAs($this->portalUser())
            ->get(route('portal.scoring'));

        $response->assertOk()
            ->assertSee('Top Scor Lead')
            ->assertSee('Score 98');
    }
}
