<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('location')->default('warehouse-1');
            $table->integer('quantity')->default(0);
            $table->integer('reserved')->default(0);
            $table->enum('type', ['physical','dropship'])->default('physical');
            $table->integer('min_threshold')->nullable();
            $table->timestamps();
            $table->index(['variant_id', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
