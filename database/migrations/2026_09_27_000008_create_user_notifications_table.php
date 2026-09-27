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
        if (!Schema::hasTable('user_notifications')) {
            Schema::create('user_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('target_role', 50)->nullable()->index();
                $table->unsignedBigInteger('operational_task_id')->nullable()->index();
                $table->string('title', 150);
                $table->text('message');
                $table->string('type', 30)->default('info'); // critical, urgent, warning, info, success
                $table->string('category', 50)->default('general'); // inventory, purchases, logistics, finance, sales, system
                $table->string('reference_code', 50)->nullable()->index();
                $table->string('deep_link', 255)->nullable();
                $table->string('action_label', 50)->default('Atender en 1 Clic');
                $table->boolean('is_read')->default(false)->index();
                $table->timestamp('read_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['target_role', 'is_read']);
                $table->index(['user_id', 'is_read']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
