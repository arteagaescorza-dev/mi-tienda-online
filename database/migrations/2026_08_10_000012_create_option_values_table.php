<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_type_id')->constrained('option_types')->cascadeOnDelete();
            $table->string('value'); // e.g., M, L, XL or rojo
            $table->string('label')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['option_type_id','value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('option_values');
    }
};
