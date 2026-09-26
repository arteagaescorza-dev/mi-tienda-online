<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_color_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('external_color');
            $table->string('local_color');
            $table->timestamps();
            $table->unique(['supplier_id', 'external_color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_color_mappings');
    }
};