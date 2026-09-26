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
        // 1. Payout Batches (Lotes Bancarios y Conciliación)
        if (!Schema::hasTable('payout_batches')) {
            Schema::create('payout_batches', function (Blueprint $table) {
                $table->id();
                $table->string('payout_id_external', 100)->unique();
                $table->string('gateway', 40); // 'stripe' | 'mercadopago' | 'oxxo_pay' | 'spei'
                $table->decimal('gross_amount', 12, 2)->default(0.00);
                $table->decimal('fee_amount', 12, 2)->default(0.00);
                $table->decimal('tax_withheld', 12, 2)->default(0.00);
                $table->decimal('net_deposited', 12, 2)->default(0.00);
                $table->string('bank_account_target', 50)->default('BBVA CLABE *9012');
                $table->string('status', 30)->default('reconciled'); // 'pending' | 'reconciled' | 'disputed'
                $table->timestamp('deposit_date')->nullable();
                $table->timestamps();

                $table->index(['gateway', 'status']);
            });
        }

        // 2. Customer Subscriptions (Membresías MRR)
        if (!Schema::hasTable('customer_subscriptions')) {
            Schema::create('customer_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('subscription_folio', 50)->unique();
                $table->string('tier_name', 100); // 'Caja Marte Nivel 1 (Básico)' | 'Caja Marte Nivel 2 (Plus)' | 'Caja Marte Nivel 3 (VIP)'
                $table->string('universe', 30)->default('marte');
                $table->decimal('monthly_amount', 10, 2)->default(599.00);
                $table->string('status', 30)->default('active'); // 'active' | 'past_due' | 'cancelled' | 'paused'
                $table->string('payment_method_token', 100)->nullable();
                $table->string('card_last4', 4)->nullable();
                $table->string('card_brand', 30)->nullable();
                $table->unsignedSmallInteger('failed_attempts_count')->default(0);
                $table->timestamp('next_billing_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'universe']);
            });
        }

        // 3. Dunning Retry Logs (Motor Dunning - TASK-SUB-DUN)
        if (!Schema::hasTable('dunning_retry_logs')) {
            Schema::create('dunning_retry_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->unsignedSmallInteger('attempt_number')->default(1);
                $table->string('decline_code', 50)->nullable(); // 'insufficient_funds' | 'card_expired' | 'generic_decline'
                $table->string('action_taken', 50); // 'automated_charge_retry' | 'whatsapp_link_sent' | 'email_dunning_sent'
                $table->boolean('was_recovered')->default(false);
                $table->decimal('amount_recovered', 10, 2)->default(0.00);
                $table->timestamp('scheduled_retry_at')->nullable();
                $table->timestamps();

                $table->foreign('subscription_id')->references('id')->on('customer_subscriptions')->cascadeOnDelete();
            });
        }

        // 4. Order Financial Margins (Margen Neto Real por Pedido - TASK-FIN-01)
        if (!Schema::hasTable('order_financial_margins')) {
            Schema::create('order_financial_margins', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->unique();
                $table->string('order_number', 50)->index();
                $table->string('universe', 30)->default('terra');
                $table->decimal('gross_revenue', 12, 2)->default(0.00);
                $table->decimal('cogs_total', 12, 2)->default(0.00);
                $table->decimal('gateway_fee', 10, 2)->default(0.00);
                $table->decimal('packaging_cost', 10, 2)->default(0.00);
                $table->decimal('shipping_courier_cost', 10, 2)->default(0.00);
                $table->decimal('net_profit', 12, 2)->default(0.00);
                $table->decimal('net_margin_percent', 6, 2)->default(0.00);
                $table->string('margin_health_level', 20)->default('healthy'); // 'healthy' (>50%) | 'standard' (30-50%) | 'critical' (<30%)
                $table->timestamps();
            });
        }

        // 5. Vendor Bills (Cuentas por Pagar - TASK-INT-19)
        if (!Schema::hasTable('vendor_bills')) {
            Schema::create('vendor_bills', function (Blueprint $table) {
                $table->id();
                $table->string('bill_number', 50)->unique();
                $table->string('vendor_type', 40); // 'apparel_workshop' | 'cosmetics_lab' | 'courier_carrier' | 'ally_settlement'
                $table->string('vendor_name', 150);
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('ally_id')->nullable();
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('bank_clabe', 30)->nullable();
                $table->string('bank_name', 60)->nullable();
                $table->string('status', 30)->default('pending'); // 'pending' | 'scheduled' | 'paid' | 'cancelled'
                $table->timestamp('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_reference', 100)->nullable();
                $table->timestamps();

                $table->index(['vendor_type', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('order_financial_margins');
        Schema::dropIfExists('dunning_retry_logs');
        Schema::dropIfExists('customer_subscriptions');
        Schema::dropIfExists('payout_batches');
    }
};
