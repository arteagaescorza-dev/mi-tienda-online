<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supplier_variant_mappings')) {
            Schema::table('supplier_variant_mappings', function (Blueprint $table) {
                $table->unique(['supplier_product_id', 'supplier_variant_key'], 'supplier_variant_key_unique');
            });

            return;
        }

        Schema::create('supplier_variant_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('supplier_variant_key');
            $table->json('attributes')->nullable();
            $table->integer('stock')->nullable();
            $table->boolean('available')->nullable();
            $table->timestamps();
            $table->unique(['supplier_product_id', 'supplier_variant_key'], 'supplier_variant_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_variant_mappings');
    }
};