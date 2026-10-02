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
        // 1. Columnas de archivado y descontinuación en products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('visible');
            }
            if (!Schema::hasColumn('products', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }
            if (!Schema::hasColumn('products', 'discontinued_reason')) {
                $table->string('discontinued_reason', 255)->nullable()->after('archived_at');
            }
        });

        // 2. Columna de frecuencia de scrapeo en suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'scrape_frequency')) {
                $table->string('scrape_frequency', 60)->default('every_6_hours')->after('extraction_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = ['is_archived', 'archived_at', 'discontinued_reason'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('products', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'scrape_frequency')) {
                $table->dropColumn('scrape_frequency');
            }
        });
    }
};
