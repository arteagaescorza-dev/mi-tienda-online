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
        // 1. Package Types (Gestor de Cajas y Cubicaje IATA 5000 - TASK-LOG-06)
        if (!Schema::hasTable('package_types')) {
            Schema::create('package_types', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 80);
                $table->string('type', 30)->default('box'); // 'box' | 'polymailer' | 'bubble_envelope'
                $table->decimal('length_cm', 6, 2);
                $table->decimal('width_cm', 6, 2);
                $table->decimal('height_cm', 6, 2);
                $table->decimal('max_weight_kg', 6, 2)->default(5.00);
                $table->decimal('unit_cost', 8, 2)->default(15.00);
                $table->integer('stock_available')->default(100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Shipping Manifests (Manifiestos Diarios de Expedición - TASK-LOG-01)
        if (!Schema::hasTable('shipping_manifests')) {
            Schema::create('shipping_manifests', function (Blueprint $table) {
                $table->id();
                $table->string('manifest_number', 50)->unique();
                $table->string('courier', 40); // 'dhl_express' | 'estafeta' | 'fedex' | 'paquetexpress'
                $table->integer('total_packages')->default(0);
                $table->string('driver_name', 100)->nullable();
                $table->string('driver_badge_or_truck', 50)->nullable();
                $table->string('status', 30)->default('open'); // 'open' | 'signed' | 'closed'
                $table->timestamp('signed_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Shipments (Bultos y Guías de Envío - TASK-LOG-01)
        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('manifest_id')->nullable();
                $table->string('tracking_number', 80)->unique();
                $table->string('courier', 40);
                $table->string('service_type', 40)->default('express'); // 'express' | 'standard' | 'economy'
                $table->decimal('weight_actual_kg', 6, 2)->default(0.50);
                $table->decimal('weight_volumetric_kg', 6, 2)->default(0.50);
                $table->decimal('shipping_cost_declared', 10, 2)->default(0.00);
                $table->decimal('shipping_cost_invoiced', 10, 2)->nullable();
                $table->string('status', 30)->default('label_created'); // 'label_created' | 'picked_up' | 'in_transit' | 'out_for_delivery' | 'delivered' | 'failed_attempt' | 'returned'
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();

                $table->index(['courier', 'status']);
                $table->foreign('manifest_id')->references('id')->on('shipping_manifests')->nullOnDelete();
            });
        }

        // 4. RMA Requests (Garantías & Devoluciones - TASK-LOG-04, TASK-INT-09)
        if (!Schema::hasTable('rma_requests')) {
            Schema::create('rma_requests', function (Blueprint $table) {
                $table->id();
                $table->string('rma_number', 50)->unique();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->string('customer_name', 120);
                $table->string('customer_phone', 30)->nullable();
                $table->string('product_sku', 60);
                $table->string('product_name', 150);
                $table->string('reason', 50); // 'factory_defect' | 'sizing_wrong' | 'allergic_reaction' | 'package_damaged'
                $table->string('sanitary_status', 40)->default('pending_inspection'); // 'pending_inspection' | 'approved_replacement' | 'approved_store_credit' | 'rejected_used_hygiene'
                $table->string('resolution_type', 40)->nullable(); // 'replacement_shipped' | 'store_credit_wallet' | 'refund'
                $table->decimal('refund_or_credit_amount', 10, 2)->default(0.00);
                $table->string('status', 30)->default('open'); // 'open' | 'in_review' | 'resolved' | 'rejected'
                $table->timestamps();

                $table->index(['status', 'sanitary_status']);
            });
        }

        // 5. RMA Inspection Logs (Inspección Sanitaria - TASK-LOG-04B)
        if (!Schema::hasTable('rma_inspection_logs')) {
            Schema::create('rma_inspection_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rma_id');
                $table->string('inspector_name', 100);
                $table->boolean('has_hygiene_seal_intact')->default(false);
                $table->boolean('has_factory_tag')->default(false);
                $table->string('verdict', 40); // 'approved' | 'rejected_sanitary_violation'
                $table->text('technical_notes')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('rma_id')->references('id')->on('rma_requests')->cascadeOnDelete();
            });
        }

        // 6. Shipping Reconciliations & Discrepancy Claims (Conciliación Fletes)
        if (!Schema::hasTable('shipping_reconciliations')) {
            Schema::create('shipping_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->string('reconciliation_folio', 50)->unique();
                $table->string('carrier', 40);
                $table->string('invoice_number', 80);
                $table->decimal('total_declared_amount', 12, 2)->default(0.00);
                $table->decimal('total_invoiced_amount', 12, 2)->default(0.00);
                $table->decimal('total_discrepancy_amount', 12, 2)->default(0.00);
                $table->string('status', 30)->default('audited'); // 'audited' | 'claims_filed' | 'resolved'
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shipping_reconciliation_items')) {
            Schema::create('shipping_reconciliation_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reconciliation_id');
                $table->string('tracking_number', 80);
                $table->decimal('declared_weight_kg', 6, 2);
                $table->decimal('invoiced_weight_kg', 6, 2);
                $table->decimal('declared_cost', 10, 2);
                $table->decimal('invoiced_cost', 10, 2);
                $table->decimal('difference_cost', 10, 2);
                $table->boolean('is_overcharged')->default(false);
                $table->timestamps();

                $table->foreign('reconciliation_id')->references('id')->on('shipping_reconciliations')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('shipping_discrepancy_claims')) {
            Schema::create('shipping_discrepancy_claims', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reconciliation_id');
                $table->string('claim_folio', 50)->unique();
                $table->decimal('claimed_amount', 12, 2);
                $table->string('status', 30)->default('submitted'); // 'submitted' | 'approved_credit' | 'denied'
                $table->text('carrier_response')->nullable();
                $table->timestamps();

                $table->foreign('reconciliation_id')->references('id')->on('shipping_reconciliations')->cascadeOnDelete();
            });
        }

        // 7. Warehouse Ramp Entries (Muelle de Carga)
        if (!Schema::hasTable('warehouse_ramp_entries')) {
            Schema::create('warehouse_ramp_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_folio', 50)->unique();
                $table->string('carrier_or_supplier', 100);
                $table->string('driver_name', 100)->nullable();
                $table->string('license_plate', 30)->nullable();
                $table->integer('packages_received_count')->default(0);
                $table->string('ramp_number', 20)->default('RAMPA_01');
                $table->string('status', 30)->default('in_dock'); // 'in_dock' | 'unloaded' | 'cleared'
                $table->timestamp('docked_at')->useCurrent();
                $table->timestamp('cleared_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_ramp_entries');
        Schema::dropIfExists('shipping_discrepancy_claims');
        Schema::dropIfExists('shipping_reconciliation_items');
        Schema::dropIfExists('shipping_reconciliations');
        Schema::dropIfExists('rma_inspection_logs');
        Schema::dropIfExists('rma_requests');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_manifests');
        Schema::dropIfExists('package_types');
    }
};
