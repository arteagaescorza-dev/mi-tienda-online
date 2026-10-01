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
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->after('icon');
            $table->string('filter_universe', 40)->nullable()->after('show_in_mobile');
            $table->string('filter_category', 80)->nullable()->after('filter_universe');
            $table->string('filter_tags', 255)->nullable()->after('filter_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'filter_universe', 'filter_category', 'filter_tags']);
        });
    }
};
