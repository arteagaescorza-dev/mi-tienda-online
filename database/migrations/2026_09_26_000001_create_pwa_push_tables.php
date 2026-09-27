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
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            Schema::create('pwa_push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('guest_cart_session', 100)->nullable();
                $table->text('endpoint');
                $table->text('p256dh_key')->nullable();
                $table->text('auth_token')->nullable();
                $table->json('topics')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('user_agent', 255)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['is_active', 'guest_cart_session']);
                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }
            });
        }

        if (!Schema::hasTable('pwa_push_notification_logs')) {
            Schema::create('pwa_push_notification_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->string('notification_type', 50)->default('general');
                $table->string('title', 150);
                $table->text('body');
                $table->string('action_url', 255)->nullable();
                $table->string('delivery_status', 30)->default('sent');
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->foreign('subscription_id')->references('id')->on('pwa_push_subscriptions')->nullOnDelete();
                $table->index(['notification_type', 'delivery_status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pwa_push_notification_logs');
        Schema::dropIfExists('pwa_push_subscriptions');
    }
};
