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
        Schema::table('cms_storefront_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_storefront_configs', 'typography_subtitle')) {
                $table->string('typography_subtitle', 100)->nullable()->default('Outfit')->after('typography_heading');
            }
            if (!Schema::hasColumn('cms_storefront_configs', 'typography_accent')) {
                $table->string('typography_accent', 100)->nullable()->default('Outfit')->after('typography_body');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_storefront_configs', function (Blueprint $table) {
            if (Schema::hasColumn('cms_storefront_configs', 'typography_subtitle')) {
                $table->dropColumn('typography_subtitle');
            }
            if (Schema::hasColumn('cms_storefront_configs', 'typography_accent')) {
                $table->dropColumn('typography_accent');
            }
        });
    }
};
