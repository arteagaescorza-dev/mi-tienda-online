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
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'priority')) {
                $table->string('priority', 30)->default('media')->after('status');
            }
            if (!Schema::hasColumn('purchase_orders', 'credit_agreement_id')) {
                $table->unsignedBigInteger('credit_agreement_id')->nullable()->after('supplier_id');
                $table->foreign('credit_agreement_id')->references('id')->on('supplier_credit_agreements')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_orders', 'gross_subtotal')) {
                $table->decimal('gross_subtotal', 12, 2)->default(0.00)->after('total_units');
            }
            if (!Schema::hasColumn('purchase_orders', 'credit_deduction_amount')) {
                $table->decimal('credit_deduction_amount', 12, 2)->default(0.00)->after('gross_subtotal');
            }
            if (!Schema::hasColumn('purchase_orders', 'carrier')) {
                $table->string('carrier', 100)->nullable()->after('currency');
            }
            if (!Schema::hasColumn('purchase_orders', 'tracking_number')) {
                $table->string('tracking_number', 100)->nullable()->after('carrier');
            }
            if (!Schema::hasColumn('purchase_orders', 'po_snapshot')) {
                $table->json('po_snapshot')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'credit_agreement_id')) {
                $table->dropForeign(['credit_agreement_id']);
                $table->dropColumn('credit_agreement_id');
            }
            $cols = ['priority', 'gross_subtotal', 'credit_deduction_amount', 'carrier', 'tracking_number', 'po_snapshot'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
