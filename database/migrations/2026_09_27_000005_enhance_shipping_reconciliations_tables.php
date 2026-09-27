<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar shipping_reconciliations (TASK-INT-08, DDL 3.41)
        if (Schema::hasTable('shipping_reconciliations')) {
            Schema::table('shipping_reconciliations', function (Blueprint $table) {
                if (!Schema::hasColumn('shipping_reconciliations', 'reconciliation_code')) {
                    $table->string('reconciliation_code', 50)->nullable()->after('id');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'carrier_id')) {
                    $table->string('carrier_id', 50)->nullable()->after('carrier');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'carrier_name')) {
                    $table->string('carrier_name', 100)->nullable()->after('carrier_id');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'invoice_folio')) {
                    $table->string('invoice_folio', 100)->nullable()->after('invoice_number');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'invoice_date')) {
                    $table->date('invoice_date')->nullable()->after('invoice_folio');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'billing_period')) {
                    $table->string('billing_period', 50)->nullable()->after('invoice_date');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'total_tracks_count')) {
                    $table->unsignedInteger('total_tracks_count')->default(0)->after('billing_period');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'matched_tracks_count')) {
                    $table->unsignedInteger('matched_tracks_count')->default(0)->after('total_tracks_count');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'discrepant_tracks_count')) {
                    $table->unsignedInteger('discrepant_tracks_count')->default(0)->after('matched_tracks_count');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'total_expected_amount')) {
                    $table->decimal('total_expected_amount', 12, 2)->default(0.00)->after('total_discrepancy_amount');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'total_overcharge_amount')) {
                    $table->decimal('total_overcharge_amount', 12, 2)->default(0.00)->after('total_expected_amount');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'total_recovered_amount')) {
                    $table->decimal('total_recovered_amount', 12, 2)->default(0.00)->after('total_overcharge_amount');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'imported_file_name')) {
                    $table->string('imported_file_name', 255)->nullable()->after('status');
                }
                if (!Schema::hasColumn('shipping_reconciliations', 'audited_by')) {
                    $table->string('audited_by', 100)->default('Mickey RevOps')->after('imported_file_name');
                }
            });
        }

        // 2. Ampliar shipping_reconciliation_items
        if (Schema::hasTable('shipping_reconciliation_items')) {
            Schema::table('shipping_reconciliation_items', function (Blueprint $table) {
                if (!Schema::hasColumn('shipping_reconciliation_items', 'shipping_reconciliation_id')) {
                    $table->unsignedBigInteger('shipping_reconciliation_id')->nullable()->after('reconciliation_id');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'order_number')) {
                    $table->string('order_number', 50)->nullable()->after('tracking_number');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'shipping_manifest_id')) {
                    $table->unsignedBigInteger('shipping_manifest_id')->nullable()->after('order_number');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'package_type_id')) {
                    $table->unsignedBigInteger('package_type_id')->nullable()->after('shipping_manifest_id');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'box_code')) {
                    $table->string('box_code', 50)->nullable()->after('package_type_id');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'box_length_cm')) {
                    $table->decimal('box_length_cm', 8, 2)->nullable()->after('box_code');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'box_width_cm')) {
                    $table->decimal('box_width_cm', 8, 2)->nullable()->after('box_length_cm');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'box_height_cm')) {
                    $table->decimal('box_height_cm', 8, 2)->nullable()->after('box_width_cm');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'warehouse_weight_kg')) {
                    $table->decimal('warehouse_weight_kg', 8, 2)->default(0.00)->after('declared_weight_kg');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'warehouse_volumetric_kg')) {
                    $table->decimal('warehouse_volumetric_kg', 8, 2)->default(0.00)->after('warehouse_weight_kg');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'carrier_billed_weight_kg')) {
                    $table->decimal('carrier_billed_weight_kg', 8, 2)->default(0.00)->after('warehouse_volumetric_kg');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'expected_rate_mxn')) {
                    $table->decimal('expected_rate_mxn', 10, 2)->default(0.00)->after('declared_cost');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'carrier_invoiced_rate_mxn')) {
                    $table->decimal('carrier_invoiced_rate_mxn', 10, 2)->default(0.00)->after('expected_rate_mxn');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'overcharge_amount_mxn')) {
                    $table->decimal('overcharge_amount_mxn', 10, 2)->default(0.00)->after('difference_cost');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'overcharge_pct')) {
                    $table->decimal('overcharge_pct', 6, 2)->default(0.00)->after('overcharge_amount_mxn');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'discrepancy_reason')) {
                    $table->string('discrepancy_reason', 150)->nullable()->after('overcharge_pct');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'audit_status')) {
                    $table->string('audit_status', 40)->default('matched')->after('discrepancy_reason');
                }
                if (!Schema::hasColumn('shipping_reconciliation_items', 'shipping_discrepancy_claim_id')) {
                    $table->unsignedBigInteger('shipping_discrepancy_claim_id')->nullable()->after('audit_status');
                }
            });
        }

        // 3. Ampliar shipping_discrepancy_claims
        if (Schema::hasTable('shipping_discrepancy_claims')) {
            Schema::table('shipping_discrepancy_claims', function (Blueprint $table) {
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'shipping_reconciliation_id')) {
                    $table->unsignedBigInteger('shipping_reconciliation_id')->nullable()->after('reconciliation_id');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'carrier_id')) {
                    $table->string('carrier_id', 50)->nullable()->after('claim_folio');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'disputed_items_count')) {
                    $table->unsignedInteger('disputed_items_count')->default(0)->after('carrier_id');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'total_disputed_amount')) {
                    $table->decimal('total_disputed_amount', 12, 2)->default(0.00)->after('claimed_amount');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'carrier_ticket_ref')) {
                    $table->string('carrier_ticket_ref', 100)->nullable()->after('total_disputed_amount');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'claim_technical_notes')) {
                    $table->text('claim_technical_notes')->nullable()->after('carrier_response');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'claim_dossier_url')) {
                    $table->string('claim_dossier_url', 255)->nullable()->after('claim_technical_notes');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('claim_dossier_url');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'resolved_at')) {
                    $table->timestamp('resolved_at')->nullable()->after('submitted_at');
                }
                if (!Schema::hasColumn('shipping_discrepancy_claims', 'filed_by')) {
                    $table->string('filed_by', 100)->default('Mickey RevOps')->after('resolved_at');
                }
            });
        }
    }

    public function down(): void
    {
        // No-op seguro para migracion aditiva
    }
};
