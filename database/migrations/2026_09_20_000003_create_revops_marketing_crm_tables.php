<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Lead Interactions (Historial CRM)
        if (!Schema::hasTable('lead_interactions')) {
            Schema::create('lead_interactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->string('channel', 30)->default('whatsapp'); // 'whatsapp' | 'email' | 'web_chat' | 'checkout'
                $table->string('interaction_type', 50); // 'cart_abandoned' | 'order_placed' | 'bot_query' | 'coupon_applied'
                $table->text('summary')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
                $table->index(['lead_id', 'channel']);
            });
        }

        // 2. Ad Campaigns (Telemetría Ads & Meta CAPI - TASK-MKT-01)
        if (!Schema::hasTable('ad_campaigns')) {
            Schema::create('ad_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('platform', 30)->default('meta_ads'); // 'meta_ads' | 'google_ads'
                $table->string('campaign_id_external', 100)->unique();
                $table->string('name', 150);
                $table->string('universe', 30)->default('terra');
                $table->string('status', 20)->default('active'); // 'active' | 'paused' | 'fatigued'
                $table->decimal('daily_budget', 10, 2)->default(0.00);
                $table->decimal('spend_today', 10, 2)->default(0.00);
                $table->decimal('platform_roas', 5, 2)->default(0.00);
                $table->decimal('net_real_roas', 5, 2)->default(0.00);
                $table->decimal('cpa', 8, 2)->default(0.00);
                $table->decimal('ctr', 5, 2)->default(0.00);
                $table->boolean('fatigue_alert')->default(false);
                $table->timestamps();

                $table->index(['platform', 'status', 'universe']);
            });
        }

        // 3. Coupons (Gestor de Cupones y Promociones)
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 40)->unique();
                $table->string('discount_type', 30)->default('percent'); // 'percent' | 'fixed_amount' | 'free_shipping' | 'bundle_gift'
                $table->decimal('discount_value', 10, 2)->default(0.00);
                $table->decimal('min_order_amount', 10, 2)->default(0.00);
                $table->string('target_universe', 30)->default('all');
                $table->unsignedInteger('max_uses')->nullable();
                $table->unsignedInteger('used_count')->default(0);
                $table->decimal('revenue_generated', 12, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index(['code', 'is_active']);
            });
        }

        // 4. Email Automations (Flujos de Emailing)
        if (!Schema::hasTable('email_automations')) {
            Schema::create('email_automations', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 80)->unique();
                $table->string('title', 150);
                $table->string('trigger_event', 60); // 'user.registered' | 'cart.abandoned' | 'order.delivered' | 'subscription.dunning'
                $table->unsignedSmallInteger('delay_minutes')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->decimal('open_rate', 5, 2)->default(0.00);
                $table->decimal('click_rate', 5, 2)->default(0.00);
                $table->decimal('revenue_attributed', 12, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. Chatbot Conversations (Chatbot Marteaga & WhatsApp Omnicanal)
        if (!Schema::hasTable('chatbot_conversations')) {
            Schema::create('chatbot_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->string('channel', 30)->default('whatsapp'); // 'whatsapp' | 'web_chat'
                $table->string('session_status', 30)->default('bot_active'); // 'bot_active' | 'human_takeover' | 'resolved'
                $table->string('customer_name', 120)->nullable();
                $table->string('customer_phone', 30)->nullable();
                $table->string('current_intent', 60)->nullable();
                $table->json('messages_json');
                $table->timestamps();

                $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
                $table->index(['session_status', 'channel']);
            });
        }

        // 6. Cart Recovery Tokens (Recuperación WhatsApp en 1 Clic - TASK-INT-05)
        if (!Schema::hasTable('cart_recovery_tokens')) {
            Schema::create('cart_recovery_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token', 100)->unique();
                $table->unsignedBigInteger('cart_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('discount_code_attached', 40)->nullable();
                $table->timestamp('expires_at');
                $table->boolean('is_redeemed')->default(false);
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_recovery_tokens');
        Schema::dropIfExists('chatbot_conversations');
        Schema::dropIfExists('email_automations');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('ad_campaigns');
        Schema::dropIfExists('lead_interactions');
    }
};
