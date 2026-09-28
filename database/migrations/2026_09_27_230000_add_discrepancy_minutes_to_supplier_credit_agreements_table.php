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
        Schema::table('supplier_credit_agreements', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_credit_agreements', 'discrepancy_type')) {
                $table->string('discrepancy_type', 50)->nullable()->after('agreement_type');
            }
            if (!Schema::hasColumn('supplier_credit_agreements', 'missing_units_count')) {
                $table->integer('missing_units_count')->default(0)->after('discrepancy_type');
            }
            if (!Schema::hasColumn('supplier_credit_agreements', 'damaged_units_count')) {
                $table->integer('damaged_units_count')->default(0)->after('missing_units_count');
            }
            if (!Schema::hasColumn('supplier_credit_agreements', 'unit_cost_applied')) {
                $table->decimal('unit_cost_applied', 10, 2)->nullable()->after('damaged_units_count');
            }
            if (!Schema::hasColumn('supplier_credit_agreements', 'authorized_by_vendor_contact')) {
                $table->string('authorized_by_vendor_contact', 150)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('supplier_credit_agreements', 'discrepancy_minutes')) {
                $table->text('discrepancy_minutes')->nullable()->after('resolution_notes');
            }
            if (!Schema::hasColumn('supplier_credit_agreements', 'applied_to_po_id')) {
                $table->unsignedBigInteger('applied_to_po_id')->nullable()->after('discrepancy_minutes');
                $table->foreign('applied_to_po_id')->references('id')->on('purchase_orders')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_credit_agreements', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_credit_agreements', 'applied_to_po_id')) {
                $table->dropForeign(['applied_to_po_id']);
                $table->dropColumn('applied_to_po_id');
            }
            $cols = [
                'discrepancy_type',
                'missing_units_count',
                'damaged_units_count',
                'unit_cost_applied',
                'authorized_by_vendor_contact',
                'discrepancy_minutes',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('supplier_credit_agreements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
