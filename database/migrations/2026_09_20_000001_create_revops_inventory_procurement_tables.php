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
        // 1. Warehouse Slots (Slotting Físico - TASK-INV-01)
        if (!Schema::hasTable('warehouse_slots')) {
            Schema::create('warehouse_slots', function (Blueprint $table) {
                $table->id();
                $table->string('slot_code', 30)->unique();
                $table->string('zone', 30)->default('ZONA_A');
                $table->string('aisle', 10)->default('P01');
                $table->string('rack', 10)->default('R01');
                $table->string('shelf', 10)->default('N1');
                $table->boolean('is_occupied')->default(false);
                $table->unsignedBigInteger('current_product_id')->nullable();
                $table->timestamps();

                $table->index(['zone', 'is_occupied']);
            });
        }

        // 2. Apparel Fabrics (Composición de Telas - TASK-INV-08)
        if (!Schema::hasTable('apparel_fabrics')) {
            Schema::create('apparel_fabrics', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80)->unique();
                $table->string('slug', 80)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Apparel Colors (Paleta y Tornasoles - TASK-INV-08)
        if (!Schema::hasTable('apparel_colors')) {
            Schema::create('apparel_colors', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80);
                $table->string('color_type', 30)->default('solid'); // 'solid' | 'gradient'
                $table->string('hex_code', 30)->default('#000000');
                $table->string('gradient_css', 200)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Cosmetic Shades (Tonos Cosmética - TASK-COS-01)
        if (!Schema::hasTable('cosmetic_shades')) {
            Schema::create('cosmetic_shades', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('shade_code', 30)->index();
                $table->string('name', 80);
                $table->string('hex_color', 30)->default('#e0ac69');
                $table->string('undertone', 30)->default('neutral'); // 'warm' | 'cool' | 'neutral'
                $table->integer('stock_physical')->default(0);
                $table->timestamps();
            });
        }

        // 5. Scraped Pending Items (Curaduría Scraping - TASK-INV-08)
        if (!Schema::hasTable('scraped_pending_items')) {
            Schema::create('scraped_pending_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('external_sku', 100)->nullable()->index();
                $table->string('external_title', 200);
                $table->decimal('external_price', 10, 2)->default(0.00);
                $table->text('external_image_url')->nullable();
                $table->string('curated_title', 200)->nullable();
                $table->string('curated_brand', 80)->nullable();
                $table->string('curated_fabric', 80)->nullable();
                $table->string('curated_color', 80)->nullable();
                $table->string('curated_design_type', 40)->default('sin_diseno'); // 'sin_diseno' | 'con_diseno'
                $table->string('curated_website_section', 40)->default('basicos'); // 'basicos' | 'disenador' | 'temporada'
                $table->decimal('curated_price', 10, 2)->nullable();
                $table->string('curation_status', 30)->default('pending'); // 'pending' | 'approved' | 'rejected'
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['curation_status', 'supplier_id']);
            });
        }

        // 6. Purchase Orders (Órdenes de Compra a Proveedores)
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number', 50)->unique();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('status', 30)->default('draft'); // 'draft'|'sent'|'confirmed'|'in_transit'|'partially_received'|'received'|'cancelled'
                $table->integer('total_units')->default(0);
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->decimal('tax', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->string('currency', 10)->default('MXN');
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('estimated_delivery_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'status']);
            });
        }

        // 7. Purchase Order Items (Curva de Tallas y Detalle de OC)
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('product_variant_id')->nullable();
                $table->string('size_name', 20)->default('M');
                $table->integer('quantity_ordered')->default(0);
                $table->integer('quantity_received')->default(0);
                $table->decimal('unit_cost', 10, 2)->default(0.00);
                $table->decimal('total_cost', 12, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            });
        }

        // 8. Purchase Kardex (Trazabilidad Mercantil de Compras)
        if (!Schema::hasTable('purchase_kardex')) {
            Schema::create('purchase_kardex', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('movement_type', 50); // 'PO_CREATED'|'PO_SENT'|'INBOUND_RECEIPT'|'COST_DISCREPANCY'|'CREDIT_NOTE_APPLIED'
                $table->text('description')->nullable();
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->timestamp('created_at')->useCurrent();

                $table->index(['purchase_order_id', 'movement_type']);
            });
        }

        // 9. Supplier Credit Agreements (Notas de Crédito y Compensaciones)
        if (!Schema::hasTable('supplier_credit_agreements')) {
            Schema::create('supplier_credit_agreements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id');
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->string('reference_folio', 50)->nullable();
                $table->string('agreement_type', 40)->default('shortage_discount'); // 'shortage_discount'|'quality_defect'|'future_po_credit'
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('status', 20)->default('pending'); // 'pending'|'applied'|'void'
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'status']);
            });
        }

        // 10. Product Bundles & Bundle Items (Recetas BOM y Cajas - TASK-INV-03)
        if (!Schema::hasTable('product_bundles')) {
            Schema::create('product_bundles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('slug', 150)->unique();
                $table->string('bundle_type', 40)->default('pack_multiple'); // 'box_subscription'|'pack_multiple'|'combo_cross'
                $table->decimal('regular_price', 10, 2)->default(0.00);
                $table->decimal('bundle_price', 10, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('bundle_items')) {
            Schema::create('bundle_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_bundle_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variant_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->timestamps();

                $table->foreign('product_bundle_id')->references('id')->on('product_bundles')->cascadeOnDelete();
            });
        }

        // 11. Cycle Counts (Auditorías Cíclicas a Ciegas - TASK-INV-07)
        if (!Schema::hasTable('cycle_counts')) {
            Schema::create('cycle_counts', function (Blueprint $table) {
                $table->id();
                $table->date('count_date');
                $table->integer('sample_sku_count')->default(3);
                $table->integer('total_units_counted')->default(0);
                $table->integer('total_variance_units')->default(0);
                $table->decimal('ira_accuracy_score', 5, 2)->default(100.00);
                $table->string('audited_by', 100)->nullable();
                $table->string('status', 30)->default('in_progress'); // 'in_progress'|'reconciled'|'flagged'
                $table->json('details_json')->nullable();
                $table->timestamps();
            });
        }

        // 12. On Demand Requests (Carritos Mixtos & Solicitudes Dropship)
        if (!Schema::hasTable('on_demand_requests')) {
            Schema::create('on_demand_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variant_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->string('status', 30)->default('pending_supplier'); // 'pending_supplier'|'po_generated'|'received'|'fulfilled'
                $table->timestamps();

                $table->index(['status', 'order_id']);
            });
        }

        // 13. Scraper Alerts (Alertas Torzo Bot)
        if (!Schema::hasTable('scraper_alerts')) {
            Schema::create('scraper_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('alert_type', 40); // 'out_of_stock'|'cost_increase'|'discontinued'|'sync_failure'
                $table->text('message');
                $table->boolean('is_resolved')->default(false);
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'is_resolved']);
            });
        }

        // 14. Stock Locks (Reserva Temporal en Checkout - TASK-INT-06)
        if (!Schema::hasTable('stock_locks')) {
            Schema::create('stock_locks', function (Blueprint $table) {
                $table->id();
                $table->string('cart_id', 100)->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('product_variant_id');
                $table->integer('quantity')->default(1);
                $table->timestamp('expires_at')->index();
                $table->string('status', 20)->default('active'); // 'active' | 'released' | 'purchased'
                $table->timestamps();
            });
        }

        // 15. BOM Deficit Procurements (Disparo Automático de OCs por BOM - TASK-INT-11)
        if (!Schema::hasTable('bom_deficit_procurements')) {
            Schema::create('bom_deficit_procurements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bundle_id');
                $table->unsignedBigInteger('component_product_id');
                $table->integer('deficit_units')->default(0);
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->string('status', 30)->default('detected'); // 'detected'|'po_drafted'|'ordered'|'fulfilled'
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_deficit_procurements');
        Schema::dropIfExists('stock_locks');
        Schema::dropIfExists('scraper_alerts');
        Schema::dropIfExists('on_demand_requests');
        Schema::dropIfExists('cycle_counts');
        Schema::dropIfExists('bundle_items');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('supplier_credit_agreements');
        Schema::dropIfExists('purchase_kardex');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('scraped_pending_items');
        Schema::dropIfExists('cosmetic_shades');
        Schema::dropIfExists('apparel_colors');
        Schema::dropIfExists('apparel_fabrics');
        Schema::dropIfExists('warehouse_slots');
    }
};
