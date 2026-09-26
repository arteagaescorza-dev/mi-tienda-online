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
        // 1. Operational Tasks (Bandeja Dinámica y SLA Aging - TASK-DSH-04, TASK-DSH-05)
        if (!Schema::hasTable('operational_tasks')) {
            Schema::create('operational_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title', 150);
                $table->string('category', 50); // 'inventory_rop'|'fulfillment_blocked'|'rma_inspection'|'finance_settlement'
                $table->string('priority', 20)->default('medium'); // 'critical'|'high'|'medium'|'low'
                $table->string('assigned_role', 50)->default('super_admin');
                $table->string('status', 20)->default('pending'); // 'pending'|'in_progress'|'completed'|'dismissed'
                $table->unsignedInteger('sla_minutes_limit')->default(120);
                $table->timestamp('due_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('action_payload')->nullable();
                $table->timestamps();

                $table->index(['status', 'priority', 'assigned_role']);
            });
        }

        // 2. Support Cases (Atención al Cliente y Ventas - TASK-DSH-01)
        if (!Schema::hasTable('support_cases')) {
            Schema::create('support_cases', function (Blueprint $table) {
                $table->id();
                $table->string('folio', 50)->unique();
                $table->string('customer_name', 120);
                $table->string('customer_email', 150)->nullable();
                $table->string('customer_phone', 30)->nullable();
                $table->string('channel', 30)->default('whatsapp'); // 'whatsapp'|'email'|'chat'|'phone'
                $table->string('subject', 150);
                $table->string('priority', 20)->default('medium');
                $table->string('status', 30)->default('open'); // 'open'|'in_progress'|'waiting_customer'|'resolved'
                $table->string('universe', 30)->default('terra');
                $table->text('notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'priority']);
            });
        }

        // 3. Fulfillment Batches (Lotes de Mesa de Expedición - TASK-DSH-02)
        if (!Schema::hasTable('fulfillment_batches')) {
            Schema::create('fulfillment_batches', function (Blueprint $table) {
                $table->id();
                $table->string('batch_number', 50)->unique();
                $table->string('status', 30)->default('picking'); // 'picking'|'packing'|'manifested'|'dispatched'
                $table->integer('orders_count')->default(0);
                $table->string('courier', 40)->default('dhl_express');
                $table->string('operator_name', 100)->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Payment Gateway Configs (Pasarelas de Pago)
        if (!Schema::hasTable('payment_gateway_configs')) {
            Schema::create('payment_gateway_configs', function (Blueprint $table) {
                $table->id();
                $table->string('gateway_key', 40)->unique(); // 'stripe'|'mercadopago'|'oxxo_pay'|'spei_transfer'
                $table->string('name', 80);
                $table->boolean('is_active')->default(true);
                $table->string('environment', 20)->default('sandbox'); // 'sandbox'|'production'
                $table->decimal('fee_percent', 5, 2)->default(3.60);
                $table->decimal('fee_fixed', 8, 2)->default(3.00);
                $table->json('credentials_encrypted')->nullable();
                $table->timestamps();
            });
        }

        // 5. API Health Logs (Telemetría de Ecosistema - TASK-DSH-03)
        if (!Schema::hasTable('api_health_logs')) {
            Schema::create('api_health_logs', function (Blueprint $table) {
                $table->id();
                $table->string('service_name', 50); // 'stripe'|'skydropx'|'meta_capi'|'scraper_torzo'|'dhl'
                $table->string('endpoint', 200)->nullable();
                $table->unsignedSmallInteger('status_code')->default(200);
                $table->unsignedInteger('response_time_ms')->default(0);
                $table->string('status_verdict', 20)->default('healthy'); // 'healthy'|'degraded'|'down'
                $table->text('error_message')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['service_name', 'status_verdict']);
            });
        }

        // 6. Webhook Event Logs (Auditoría HMAC - TASK-DSH-03)
        if (!Schema::hasTable('webhook_event_logs')) {
            Schema::create('webhook_event_logs', function (Blueprint $table) {
                $table->id();
                $table->string('gateway', 40); // 'stripe'|'mercadopago'|'skydropx'|'whatsapp'
                $table->string('event_type', 100);
                $table->string('external_event_id', 150)->nullable()->index();
                $table->boolean('is_signature_valid')->default(true);
                $table->json('payload_json')->nullable();
                $table->string('processing_status', 30)->default('processed'); // 'processed'|'failed'|'ignored'
                $table->text('error_details')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['gateway', 'event_type']);
            });
        }

        // 7. System Environment Configs & Audit Logs (Entornos Sandbox/Prod - TASK-SYS-ENV)
        if (!Schema::hasTable('system_environment_configs')) {
            Schema::create('system_environment_configs', function (Blueprint $table) {
                $table->id();
                $table->string('module', 50)->unique();
                $table->string('active_environment', 20)->default('sandbox'); // 'sandbox' | 'production'
                $table->boolean('lock_mock_payments')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('environment_audit_logs')) {
            Schema::create('environment_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('module', 50);
                $table->string('from_environment', 20);
                $table->string('to_environment', 20);
                $table->unsignedBigInteger('changed_by_user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 8. Fraud Risk Assessments (Escudo Antifraude Stripe Radar - TASK-INT-14)
        if (!Schema::hasTable('fraud_risk_assessments')) {
            Schema::create('fraud_risk_assessments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->string('risk_level', 20)->default('normal'); // 'normal' | 'elevated' | 'highest'
                $table->unsignedSmallInteger('risk_score')->default(15); // 0 - 100
                $table->string('recommendation', 30)->default('allow'); // 'allow' | 'manual_review' | 'block'
                $table->json('rule_matches_json')->nullable();
                $table->timestamps();
            });
        }

        // 9. AI Copilot Insights & Chat Messages (TASK-DSH-07)
        if (!Schema::hasTable('ai_copilot_insights')) {
            Schema::create('ai_copilot_insights', function (Blueprint $table) {
                $table->id();
                $table->string('category', 50); // 'inventory'|'marketing'|'finance'|'logistics'
                $table->string('title', 150);
                $table->text('summary');
                $table->string('action_type', 50)->nullable();
                $table->string('action_label', 100)->nullable();
                $table->boolean('is_executed')->default(false);
                $table->timestamp('executed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_copilot_chat_messages')) {
            Schema::create('ai_copilot_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('sender', 20); // 'user' | 'assistant'
                $table->text('message');
                $table->json('action_chips_json')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 10. Custom Roles & Permissions (RBAC Multiverso - TASK-RBAC-02)
        if (!Schema::hasTable('custom_roles')) {
            Schema::create('custom_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 60)->unique();
                $table->string('label', 100);
                $table->string('scope', 40)->default('all_multiverse'); // 'all_multiverse' | 'marte_only' | 'venus_only' | 'tiresias_only'
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('custom_permissions')) {
            Schema::create('custom_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80)->unique();
                $table->string('label', 120);
                $table->string('group', 40); // 'inventory'|'orders'|'finance'|'marketing'|'logistics'|'allies'
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('custom_role_permissions')) {
            Schema::create('custom_role_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');
                $table->timestamps();

                $table->foreign('role_id')->references('id')->on('custom_roles')->cascadeOnDelete();
                $table->foreign('permission_id')->references('id')->on('custom_permissions')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_role_permissions');
        Schema::dropIfExists('custom_permissions');
        Schema::dropIfExists('custom_roles');
        Schema::dropIfExists('ai_copilot_chat_messages');
        Schema::dropIfExists('ai_copilot_insights');
        Schema::dropIfExists('fraud_risk_assessments');
        Schema::dropIfExists('environment_audit_logs');
        Schema::dropIfExists('system_environment_configs');
        Schema::dropIfExists('webhook_event_logs');
        Schema::dropIfExists('api_health_logs');
        Schema::dropIfExists('payment_gateway_configs');
        Schema::dropIfExists('fulfillment_batches');
        Schema::dropIfExists('support_cases');
        Schema::dropIfExists('operational_tasks');
    }
};
