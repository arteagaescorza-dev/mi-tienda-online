<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Regla: Las curvas de tallas se pueden asociar a marcas y proveedores.
     */
    public function up(): void
    {
        Schema::table('apparel_size_curves', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('category_scope')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('supplier_id')->constrained('brands')->nullOnDelete();
            $table->index(['supplier_id', 'brand_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apparel_size_curves', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['brand_id']);
            $table->dropIndex(['supplier_id', 'brand_id']);
            $table->dropColumn(['supplier_id', 'brand_id']);
        });
    }
};

