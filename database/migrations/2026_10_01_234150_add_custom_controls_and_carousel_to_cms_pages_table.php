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
            $table->string('text_color', 30)->nullable()->default('#ffffff')->after('overlay_gradient');
            $table->string('image_source_mode', 30)->nullable()->default('custom')->after('text_color');
            $table->json('gallery_images')->nullable()->after('image_source_mode');
            $table->string('overlay_color_start', 30)->nullable()->default('#000000')->after('gallery_images');
            $table->string('overlay_color_end', 30)->nullable()->default('#000000')->after('overlay_color_start');
            $table->unsignedTinyInteger('overlay_opacity_start')->nullable()->default(25)->after('overlay_color_end');
            $table->unsignedTinyInteger('overlay_opacity_end')->nullable()->default(85)->after('overlay_opacity_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn([
                'text_color',
                'image_source_mode',
                'gallery_images',
                'overlay_color_start',
                'overlay_color_end',
                'overlay_opacity_start',
                'overlay_opacity_end',
            ]);
        });
    }
};
