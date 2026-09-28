<?php

namespace Tests\Feature;

use App\Models\CmsBlogPost;
use App\Models\CmsCatalogFilter;
use App\Models\CmsOfficialDocument;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsStorefrontConfig;
use App\Models\CmsTrackingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hub2CmsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test storefront configs can be queried and have default fallbacks.
     */
    public function test_storefront_configs_persist_and_retrieve_by_universe(): void
    {
        $marte = CmsStorefrontConfig::forUniverse('marte');
        $this->assertNotNull($marte);
        $this->assertEquals('marte', $marte->universe);
        $this->assertEquals('#09090b', $marte->primary_color);
        $this->assertEquals('#4f46e5', $marte->accent_color);

        $venus = CmsStorefrontConfig::forUniverse('venus');
        $this->assertNotNull($venus);
        $this->assertEquals('venus', $venus->universe);
        $this->assertEquals('#db2777', $venus->accent_color);
    }

    /**
     * Test sections can be created and queried by universe and active scope.
     */
    public function test_sections_can_be_ordered_and_scoped(): void
    {
        $section = CmsSection::create([
            'universe' => 'marte',
            'name' => 'Sección Test Marte',
            'type' => 'custom_html_css_js',
            'title' => 'Novedades Marte',
            'custom_html' => '<div class="banner-test">Banner</div>',
            'sort_order' => 10,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('cms_sections', [
            'name' => 'Sección Test Marte',
            'sort_order' => 10,
        ]);

        $activeForMarte = CmsSection::active()->forUniverse('marte')->get();
        $this->assertTrue($activeForMarte->contains('id', $section->id));
    }

    /**
     * Test pages navbar and footer scopes.
     */
    public function test_cms_pages_scopes_and_persistence(): void
    {
        $page = CmsPage::create([
            'slug' => 'prueba-terminos',
            'title' => 'Términos de Servicio',
            'content_html' => '<p>Contenido legal</p>',
            'show_in_navbar' => true,
            'navbar_order' => 5,
            'show_in_footer' => true,
            'footer_order' => 10,
            'status' => 'active',
        ]);

        $navbarPages = CmsPage::inNavbar()->get();
        $this->assertTrue($navbarPages->contains('id', $page->id));

        $footerPages = CmsPage::inFooter()->get();
        $this->assertTrue($footerPages->contains('id', $page->id));
    }

    /**
     * Test blog posts can be stored and queried with published scope.
     */
    public function test_cms_blog_posts_published_scope(): void
    {
        $post = CmsBlogPost::create([
            'slug' => 'articulo-prueba-textil',
            'title' => 'Artículo de Prueba Textil',
            'body' => '<p>Detalles del artículo</p>',
            'category_name' => 'Innovación',
            'tags' => ['algodón', 'modal'],
            'reading_time_minutes' => 5,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $published = CmsBlogPost::published()->get();
        $this->assertTrue($published->contains('id', $post->id));
        $this->assertEquals(['algodón', 'modal'], $post->tags);
    }

    /**
     * Test official documents retrieval by code.
     */
    public function test_official_documents_retrieval_by_code(): void
    {
        CmsOfficialDocument::create([
            'code' => 'SIZE_GUIDE_TEST',
            'title' => 'Guía de Tallas Test',
            'payload' => [
                ['size' => 'M', 'waist' => '80-84cm'],
            ],
            'version' => '1.0',
            'is_active' => true,
        ]);

        $data = CmsOfficialDocument::getByCode('SIZE_GUIDE_TEST');
        $this->assertIsArray($data);
        $this->assertEquals('M', $data[0]['size']);
    }

    /**
     * Test tracking settings singleton retrieval.
     */
    public function test_tracking_settings_retrieval_and_update(): void
    {
        $tracking = CmsTrackingSetting::current();
        $this->assertInstanceOf(CmsTrackingSetting::class, $tracking);

        $tracking->update([
            'gtm_id' => 'GTM-TEST123',
            'is_active' => true,
        ]);

        $fresh = CmsTrackingSetting::current();
        $this->assertEquals('GTM-TEST123', $fresh->gtm_id);
        $this->assertTrue($fresh->is_active);
    }

    /**
     * Test catalog filters visible scope and ordering.
     */
    public function test_catalog_filters_ordering_and_visibility(): void
    {
        CmsCatalogFilter::create([
            'filter_key' => 'custom_filter_test',
            'label' => 'Filtro Test',
            'display_type' => 'pills',
            'sort_order' => 99,
            'is_visible' => true,
        ]);

        $visibleFilters = CmsCatalogFilter::visible()->get();
        $this->assertTrue($visibleFilters->contains('filter_key', 'custom_filter_test'));
    }
}
