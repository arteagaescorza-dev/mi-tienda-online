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
        Schema::table('scraped_pending_items', function (Blueprint $table) {
            if (!Schema::hasColumn('scraped_pending_items', 'supplier_product_id')) {
                $table->unsignedBigInteger('supplier_product_id')->nullable()->after('supplier_id')->index();
            }
            if (!Schema::hasColumn('scraped_pending_items', 'suggested_category')) {
                $table->string('suggested_category', 100)->nullable()->after('external_title');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'suggested_subcategory')) {
                $table->string('suggested_subcategory', 100)->nullable()->after('suggested_category');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'suggested_fabric')) {
                $table->string('suggested_fabric', 100)->nullable()->after('suggested_subcategory');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'detected_color_name')) {
                $table->string('detected_color_name', 100)->nullable()->after('suggested_fabric');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'detected_color_hex')) {
                $table->string('detected_color_hex', 50)->nullable()->after('detected_color_name');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'color_type')) {
                $table->string('color_type', 50)->default('solid')->after('detected_color_hex');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'origin_url')) {
                $table->text('origin_url')->nullable()->after('external_image_url');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'scraped_cost')) {
                $table->decimal('scraped_cost', 10, 2)->default(0.00)->after('external_price');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'supplier_stock')) {
                $table->integer('supplier_stock')->default(0)->after('scraped_cost');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'sizes')) {
                $table->json('sizes')->nullable()->after('supplier_stock');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'images')) {
                $table->json('images')->nullable()->after('sizes');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'raw_snapshot')) {
                $table->json('raw_snapshot')->nullable()->after('images');
            }
            if (!Schema::hasColumn('scraped_pending_items', 'scraped_description')) {
                $table->text('scraped_description')->nullable()->after('raw_snapshot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraped_pending_items', function (Blueprint $table) {
            $cols = [
                'supplier_product_id', 'suggested_category', 'suggested_subcategory',
                'suggested_fabric', 'detected_color_name', 'detected_color_hex', 'color_type',
                'origin_url', 'scraped_cost', 'supplier_stock', 'sizes', 'images',
                'raw_snapshot', 'scraped_description'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('scraped_pending_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
