<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_sku');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_url')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('availability', ['unknown','in_stock','out_of_stock'])->default('unknown');
            $table->json('raw_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['supplier_id','supplier_sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
