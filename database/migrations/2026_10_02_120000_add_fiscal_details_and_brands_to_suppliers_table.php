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
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'tax_id')) {
                $table->string('tax_id', 50)->nullable()->after('vendor_code');
            }
            if (!Schema::hasColumn('suppliers', 'fiscal_regime')) {
                $table->string('fiscal_regime', 150)->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('suppliers', 'fiscal_address')) {
                $table->text('fiscal_address')->nullable()->after('fiscal_regime');
            }
            if (!Schema::hasColumn('suppliers', 'fiscal_zip')) {
                $table->string('fiscal_zip', 20)->nullable()->after('fiscal_address');
            }
            if (!Schema::hasColumn('suppliers', 'invoice_email')) {
                $table->string('invoice_email', 150)->nullable()->after('contact_email');
            }
            if (!Schema::hasColumn('suppliers', 'origin')) {
                $table->string('origin', 30)->default('manual')->after('type');
            }
            if (!Schema::hasColumn('suppliers', 'brands')) {
                $table->json('brands')->nullable()->after('credentials');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $cols = [];
            foreach (['legal_name', 'tax_id', 'fiscal_regime', 'fiscal_address', 'fiscal_zip', 'invoice_email', 'origin', 'brands'] as $col) {
                if (Schema::hasColumn('suppliers', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
