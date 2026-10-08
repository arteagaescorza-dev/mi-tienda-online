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
        // 1. Configuraciones de vitrinas por universo (colores, títulos, estilos y SEO)
        Schema::create('cms_storefront_configs', function (Blueprint $table) {
            $table->id();
            $table->string('universe', 32)->unique(); // marte, venus, tiresias, terra
            $table->string('brand_name', 100);
            $table->string('sub_title', 150)->nullable();
            $table->string('kicker', 150)->nullable();
            $table->string('hero_tag', 150)->nullable();
            $table->string('hero_title', 255)->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_cta_text', 100)->nullable();
            $table->string('hero_cta_url', 255)->nullable();
            $table->string('page_title', 200)->nullable();
            $table->string('primary_color', 20)->default('#09090b');
            $table->string('secondary_color', 20)->nullable();
            $table->string('accent_color', 20)->default('#4f46e5');
            $table->string('background_color', 20)->default('#fafafa');
            $table->string('surface_color', 20)->default('#ffffff');
            $table->string('text_color', 20)->default('#0f172a');
            $table->string('typography_heading', 100)->default('Plus Jakarta Sans');
            $table->string('typography_body', 100)->default('Plus Jakarta Sans');
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->json('seo_meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Secciones dinámicas modulares del Home (arrastrables con SortableJS)
        Schema::create('cms_sections', function (Blueprint $table) {
            $table->id();
            $table->string('universe', 32)->default('all'); // all, marte, venus, tiresias, terra
            $table->string('name', 120);
            $table->string('type', 60); // hero_carousel, brand_pillars, categories_grid, family_showcase, tags_cloud, custom_html_css_js, curated_products, editorial_banner
            $table->string('title', 200)->nullable();
            $table->text('subtitle')->nullable();
            $table->string('badge', 80)->nullable();
            $table->json('query_rules')->nullable(); // Filtros de categorías, marcas, familias de Hub 4
            $table->longText('custom_html')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->json('settings')->nullable(); // Columnas, animaciones, CTA
            $table->integer('sort_order')->default(0);
            $table->string('status', 20)->default('active'); // active, draft, archived
            $table->timestamps();

            $table->index(['universe', 'status', 'sort_order']);
        });

        // 3. Páginas dinámicas sincronizadas con el Navbar y Footer
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 150)->unique();
            $table->string('title', 200);
            $table->string('icon', 60)->nullable();
            $table->longText('content_html')->nullable();
            $table->boolean('show_in_navbar')->default(false);
            $table->integer('navbar_order')->default(0);
            $table->boolean('show_in_footer')->default(false);
            $table->integer('footer_order')->default(0);
            $table->string('seo_title', 200)->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords', 255)->nullable();
            $table->string('status', 20)->default('active'); // active, draft, archived
            $table->timestamps();

            $table->index(['show_in_navbar', 'navbar_order']);
        });

        // 4. Revista Digital / Blog
        Schema::create('cms_blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('title', 250);
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('featured_image', 255)->nullable();
            $table->string('category_name', 100)->default('Estilo & Bienestar');
            $table->json('tags')->nullable();
            $table->string('author_name', 120)->default('Editorial Torzo');
            $table->integer('reading_time_minutes')->default(3);
            $table->json('seo_meta')->nullable();
            $table->string('status', 20)->default('published'); // draft, published, archived
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        // 5. Documentos normativos, guías de tallas y protocolos sanitarios oficiales
        Schema::create('cms_official_documents', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique(); // SIZE_GUIDE_TORZO, CARE_PROTOCOLS, PROFECO_SANITARY_NOTICE
            $table->string('title', 200);
            $table->json('payload'); // Tabla de tallas, listado de cuidados o texto normativo
            $table->string('version', 20)->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Configuración de etiquetas y analítica (GTM, GA4, Meta Pixel y Conversions API)
        Schema::create('cms_tracking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gtm_id', 50)->nullable(); // GTM-XXXXXXX
            $table->string('ga4_measurement_id', 50)->nullable(); // G-XXXXXXXXXX
            $table->string('facebook_pixel_id', 50)->nullable(); // 1234567890
            $table->text('facebook_capi_token')->nullable(); // Token de API de conversiones
            $table->string('facebook_test_event_code', 50)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 7. Editor de facetas y filtros del catálogo (Universos, Familias, Categorías, Tallas)
        Schema::create('cms_catalog_filters', function (Blueprint $table) {
            $table->id();
            $table->string('filter_key', 60)->unique(); // universe, brand, category, size, price, tag
            $table->string('label', 100);
            $table->string('display_type', 40)->default('checkbox'); // checkbox, range, pills, swatches
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->json('config')->nullable(); // Opciones fijas o reglas de extracción
            $table->timestamps();

            $table->index(['is_visible', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_catalog_filters');
        Schema::dropIfExists('cms_tracking_settings');
        Schema::dropIfExists('cms_official_documents');
        Schema::dropIfExists('cms_blog_posts');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('cms_sections');
        Schema::dropIfExists('cms_storefront_configs');
    }
};
