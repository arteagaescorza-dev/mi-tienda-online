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
        // 1. Allies (Directorio de Aliados - TASK-ALY-01)
        if (!Schema::hasTable('allies')) {
            Schema::create('allies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->string('category', 50); // 'circuit_party' | 'onlyfans_creator' | 'sauna' | 'drag_ballroom' | 'beauty_spa'
                $table->string('category_label', 100)->nullable();
                $table->string('universe', 30)->default('marte'); // 'marte' | 'venus' | 'tiresias' | 'terra'
                $table->string('contact_name', 120)->nullable();
                $table->string('contact_whatsapp', 30)->nullable();
                $table->string('contact_email', 150)->nullable();
                $table->string('social_handle', 80)->nullable();
                $table->string('portal_token', 100)->unique();
                $table->string('commission_type', 30)->default('percentage'); // 'percentage' | 'fixed_per_order'
                $table->decimal('commission_rate', 5, 2)->default(12.00); // 12%
                $table->decimal('lead_acquisition_cost', 8, 2)->default(20.00); // $20 MXN por lead Dir A
                $table->string('bank_clabe', 30)->nullable();
                $table->string('bank_name', 60)->nullable();
                $table->decimal('clearing_balance', 12, 2)->default(0.00);
                $table->string('status', 20)->default('active'); // 'active' | 'paused' | 'terminated'
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['universe', 'status', 'portal_token']);
            });
        }

        // 2. Ally Campaigns Dir A (Web -> Aliado / Lead Magnet - TASK-ALY-02)
        if (!Schema::hasTable('ally_campaigns_dir_a')) {
            Schema::create('ally_campaigns_dir_a', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ally_id');
                $table->string('title', 150);
                $table->string('slug', 150)->unique();
                $table->string('universe', 30)->default('marte');
                $table->string('benefit', 200);
                $table->text('instructions')->nullable();
                $table->string('display_format', 40)->default('popup_modal'); // 'popup_modal' | 'top_marquee' | 'pdp_inline_banner'
                $table->unsignedInteger('leads_count')->default(0);
                $table->string('status', 20)->default('active'); // 'active' | 'ended'
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('banner_image', 200)->nullable();
                $table->timestamps();

                $table->foreign('ally_id')->references('id')->on('allies')->cascadeOnDelete();
            });
        }

        // 3. Ally Lead Captures (Prospectos Capturados con Data Masking & CAPI - TASK-ALY-02)
        if (!Schema::hasTable('ally_lead_captures')) {
            Schema::create('ally_lead_captures', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_code', 50)->unique();
                $table->unsignedBigInteger('campaign_id')->nullable();
                $table->unsignedBigInteger('ally_id');
                $table->string('lead_name', 120);
                $table->string('lead_whatsapp', 30);
                $table->string('lead_email', 150)->nullable();
                $table->string('universe', 30)->default('marte');
                $table->boolean('is_redeemed')->default(false);
                $table->timestamp('redeemed_at')->nullable();
                $table->string('redeemed_by', 100)->nullable();
                $table->decimal('capi_match_score', 4, 2)->default(9.80);
                $table->timestamps();

                $table->foreign('ally_id')->references('id')->on('allies')->cascadeOnDelete();
                $table->index(['voucher_code', 'is_redeemed']);
            });
        }

        // 4. Ally Coupons Dir B (Aliado -> Web / Cupones Deterministas & UTM - TASK-ALY-03)
        if (!Schema::hasTable('ally_coupons_dir_b')) {
            Schema::create('ally_coupons_dir_b', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ally_id');
                $table->string('coupon_code', 50)->unique();
                $table->decimal('discount_pct', 5, 2)->default(15.00);
                $table->decimal('commission_rate', 5, 2)->default(12.00);
                $table->string('universe', 30)->default('marte');
                $table->string('utm_source', 60);
                $table->string('utm_campaign', 60);
                $table->unsignedInteger('clicks_count')->default(0);
                $table->unsignedInteger('orders_count')->default(0);
                $table->decimal('revenue_attributed', 12, 2)->default(0.00);
                $table->decimal('commission_earned', 12, 2)->default(0.00);
                $table->string('status', 20)->default('active');
                $table->timestamps();

                $table->foreign('ally_id')->references('id')->on('allies')->cascadeOnDelete();
            });
        }

        // 5. Ally Clearing Settlements (Cámara de Compensación - TASK-ALY-04, TASK-INT-19)
        if (!Schema::hasTable('ally_clearing_settlements')) {
            Schema::create('ally_clearing_settlements', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->unsignedBigInteger('ally_id');
                $table->string('period_label', 60);
                $table->decimal('leads_credit', 12, 2)->default(0.00);
                $table->decimal('commissions_debit', 12, 2)->default(0.00);
                $table->decimal('net_balance', 12, 2)->default(0.00);
                $table->string('settlement_type', 40)->default('spei_transfer'); // 'spei_transfer' | 'in_kind_apparel' | 'sponsorship_vip'
                $table->string('status', 30)->default('executed'); // 'scheduled' | 'executed' | 'cancelled'
                $table->string('accounting_reference', 120)->nullable();
                $table->timestamp('settled_at')->nullable();
                $table->timestamps();

                $table->foreign('ally_id')->references('id')->on('allies')->cascadeOnDelete();
                $table->index(['ally_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ally_clearing_settlements');
        Schema::dropIfExists('ally_coupons_dir_b');
        Schema::dropIfExists('ally_lead_captures');
        Schema::dropIfExists('ally_campaigns_dir_a');
        Schema::dropIfExists('allies');
    }
};
