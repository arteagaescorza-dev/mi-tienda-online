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
            if (!Schema::hasColumn('cms_pages', 'title_color')) {
                $table->string('title_color', 30)->nullable()->default('#ffffff')->after('text_color');
            }
            if (!Schema::hasColumn('cms_pages', 'gradient_direction')) {
                $table->string('gradient_direction', 50)->nullable()->default('to bottom')->after('overlay_color_end');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            if (Schema::hasColumn('cms_pages', 'title_color')) {
                $table->dropColumn('title_color');
            }
            if (Schema::hasColumn('cms_pages', 'gradient_direction')) {
                $table->dropColumn('gradient_direction');
            }
        });
    }
};
