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
            if (!Schema::hasColumn('cms_storefront_configs', 'logo_url')) {
                $table->string('logo_url', 255)->nullable()->after('brand_name');
            }
            if (!Schema::hasColumn('cms_storefront_configs', 'logo_mobile_url')) {
                $table->string('logo_mobile_url', 255)->nullable()->after('logo_url');
            }
            if (!Schema::hasColumn('cms_storefront_configs', 'favicon_url')) {
                $table->string('favicon_url', 255)->nullable()->after('logo_mobile_url');
            }
            if (!Schema::hasColumn('cms_storefront_configs', 'border_color')) {
                $table->string('border_color', 20)->default('#e4e4e7')->after('text_color');
            }
            if (!Schema::hasColumn('cms_storefront_configs', 'muted_text_color')) {
                $table->string('muted_text_color', 20)->default('#71717a')->after('border_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_storefront_configs', function (Blueprint $table) {
            $table->dropColumn([
                'logo_url',
                'logo_mobile_url',
                'favicon_url',
                'border_color',
                'muted_text_color',
            ]);
        });
    }
};
