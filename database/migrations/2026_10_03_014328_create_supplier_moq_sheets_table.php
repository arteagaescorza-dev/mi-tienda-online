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
        Schema::create('supplier_moq_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_name', 150);
            $table->string('title', 200);
            $table->string('validity', 100)->nullable();
            $table->string('currency', 10)->default('MXN');
            $table->json('tiers'); // Escalas configurables: [{ key: 'menudeo', label: 'Menudeo', min_qty: 1 }, { key: 'moq_50', label: '50 prendas', min_qty: 50 }, ...]
            $table->json('rows');  // Precios por categoría/prenda: [{ category: 'Tanga', prices: { menudeo: 180, moq_50: 90, ... } }, ...]
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'moq_sheet_id')) {
                    $table->foreignId('moq_sheet_id')->nullable()->after('supplier_id')->constrained('supplier_moq_sheets')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_orders', 'moq_tier_label')) {
                    $table->string('moq_tier_label', 100)->nullable()->after('moq_sheet_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_orders', 'moq_sheet_id')) {
                    $table->dropForeign(['moq_sheet_id']);
                    $table->dropColumn(['moq_sheet_id', 'moq_tier_label']);
                }
            });
        }

        Schema::dropIfExists('supplier_moq_sheets');
    }
};
