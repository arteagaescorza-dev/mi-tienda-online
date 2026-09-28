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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('role')
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->foreignId('ally_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('allies')
                ->nullOnDelete();

            $table->index(['role', 'supplier_id']);
            $table->index(['role', 'ally_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['ally_id']);
            $table->dropIndex(['role', 'supplier_id']);
            $table->dropIndex(['role', 'ally_id']);
            $table->dropColumn(['supplier_id', 'ally_id']);
        });
    }
};
