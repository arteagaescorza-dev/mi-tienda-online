<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Three-Way Matching Inbound (TASK-INT-07, DDL 3.33)
        if (!Schema::hasTable('three_way_matchings')) {
            Schema::create('three_way_matchings', function (Blueprint $table) {
                $table->id();
                $table->string('po_id', 50);
                $table->string('po_code', 50);
                $table->string('supplier_id', 50);
                $table->string('supplier_name', 150);
                $table->unsignedInteger('po_qty');
                $table->decimal('po_total_amount', 12, 2);
                $table->unsignedInteger('warehouse_received_qty');
                $table->unsignedInteger('warehouse_damaged_qty')->default(0);
                $table->string('invoice_number', 80);
                $table->unsignedInteger('invoice_qty');
                $table->decimal('invoice_gross_amount', 12, 2);
                $table->string('match_status', 40)->default('discrepancy_held'); // 'matched' | 'discrepancy_held' | 'debit_note_issued' | 'approved_for_payment'
                $table->string('debit_note_number', 80)->nullable();
                $table->decimal('debit_note_amount', 12, 2)->default(0.00);
                $table->decimal('payable_net_amount', 12, 2)->default(0.00);
                $table->boolean('spei_payout_blocked')->default(true);
                $table->text('discrepancy_reason')->nullable();
                $table->string('inspected_by', 100)->nullable()->default('Rampa Control Central');
                $table->unsignedBigInteger('vendor_bill_id')->nullable();
                $table->timestamps();

                $table->index(['po_code', 'supplier_id']);
                $table->index(['match_status', 'spei_payout_blocked']);
            });
        }

        // 2. Tax Ledger Entries - Motor Fiscal SAT (TASK-FIN-02, DDL 3.33)
        if (!Schema::hasTable('tax_ledger_entries')) {
            Schema::create('tax_ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->string('period_month', 7); // YYYY-MM
                $table->date('transaction_date');
                $table->string('order_reference', 80);
                $table->decimal('pvp_gross_amount', 12, 2);
                $table->decimal('taxable_base', 12, 2);
                $table->decimal('iva_transferred_16', 12, 2);
                $table->decimal('iva_creditable_16', 12, 2)->default(0.00);
                $table->decimal('platform_retention_isr_1', 12, 2)->default(0.00);
                $table->decimal('platform_retention_iva_8', 12, 2)->default(0.00);
                $table->decimal('net_iva_payable', 12, 2);
                $table->string('channel_gateway', 40)->default('stripe');
                $table->string('cfdi_uuid', 64)->nullable();
                $table->timestamps();

                $table->index(['period_month', 'channel_gateway']);
                $table->index('order_reference');
            });
        }

        // 3. Vendor Shipments B2B (TASK-INT-20, DDL 3.33)
        if (!Schema::hasTable('vendor_shipments')) {
            Schema::create('vendor_shipments', function (Blueprint $table) {
                $table->id();
                $table->string('po_id', 50);
                $table->string('po_code', 50);
                $table->string('supplier_id', 50);
                $table->string('carrier_name', 100);
                $table->string('tracking_number', 100);
                $table->unsignedInteger('pieces_dispatched');
                $table->string('remission_number', 80)->nullable();
                $table->string('status', 40)->default('in_transit');
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();

                $table->index(['po_code', 'supplier_id']);
                $table->index('status');
            });
        }

        // 4. Ampliar vendor_bills para integracion con 3-way matching
        if (Schema::hasTable('vendor_bills')) {
            Schema::table('vendor_bills', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_bills', 'three_way_matching_id')) {
                    $table->unsignedBigInteger('three_way_matching_id')->nullable()->after('ally_id');
                }
                if (!Schema::hasColumn('vendor_bills', 'is_spei_blocked')) {
                    $table->boolean('is_spei_blocked')->default(false)->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_shipments');
        Schema::dropIfExists('tax_ledger_entries');
        Schema::dropIfExists('three_way_matchings');
    }
};
