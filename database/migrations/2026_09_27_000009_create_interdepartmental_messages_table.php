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
        if (!Schema::hasTable('interdepartmental_messages')) {
            Schema::create('interdepartmental_messages', function (Blueprint $table) {
                $table->id();
                $table->string('channel', 50)->index(); // 'almacen', 'ventas', 'logistica', 'general'
                $table->unsignedBigInteger('sender_id')->nullable()->index();
                $table->string('sender_name', 120);
                $table->string('sender_role', 50)->default('operator');
                $table->string('target_role', 50)->nullable();
                $table->text('message');
                $table->string('reference_type', 50)->nullable(); // order, po, rma, sku, claim, shipment
                $table->string('reference_code', 100)->nullable()->index();
                $table->string('priority', 20)->default('normal'); // normal, high, urgent
                $table->boolean('is_resolved')->default(false)->index();
                $table->timestamp('resolved_at')->nullable();
                $table->string('resolved_by', 120)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['channel', 'is_resolved']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interdepartmental_messages');
    }
};
