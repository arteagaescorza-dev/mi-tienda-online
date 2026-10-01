<?php

namespace Tests\Feature;

use App\Models\CmsBlogPost;
use App\Models\CmsCatalogFilter;
use App\Models\CmsPage;
use App\Models\CmsTrackingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hub2CmsPagesAndBlogTest extends TestCase
{
    use RefreshDatabase;

    protected User $portalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->portalUser = User::factory()->create(['role' => 'portal']);
    }

    public function test_admin_can_create_update_and_delete_cms_page(): void
    {
        // 1. Create Page
        $createResp = $this->actingAs($this->portalUser)->post(route('portal.store.pages.store'), [
            'title' => 'Política de Devoluciones y Garantía',
            'slug' => 'garantia-y-devoluciones',
            'icon' => 'shield-check',
            'content_html' => '<h2>Garantía Oficial Torzo</h2><p>Prendas garantizadas contra cualquier defecto de confección.</p>',
            'show_in_navbar' => 1,
            'navbar_order' => 5,
            'show_in_footer' => 1,
            'footer_order' => 5,
            'seo_title' => 'Garantía Oficial | Multiverso Store',
            'seo_description' => 'Conoce nuestra garantía en costuras y textiles anatómicos.',
            'status' => 'active',
        ]);

        $createResp->assertRedirect();
        $this->assertDatabaseHas('cms_pages', [
            'slug' => 'garantia-y-devoluciones',
            'show_in_navbar' => true,
        ]);

        $page = CmsPage::where('slug', 'garantia-y-devoluciones')->firstOrFail();

        // 2. Update Page
        $updateResp = $this->actingAs($this->portalUser)->put(route('portal.store.pages.update', $page->id), [
            'title' => 'Política de Devoluciones Actualizada',
            'slug' => 'garantia-y-devoluciones',
            'icon' => 'shield-alert',
            'content_html' => '<p>Texto actualizado de garantía.</p>',
            'show_in_navbar' => 0,
            'navbar_order' => 10,
            'show_in_footer' => 1,
            'footer_order' => 6,
            'seo_title' => 'Garantía Actualizada',
            'status' => 'active',
        ]);

        $updateResp->assertRedirect();
        $this->assertDatabaseHas('cms_pages', [
            'id' => $page->id,
            'title' => 'Política de Devoluciones Actualizada',
            'show_in_navbar' => false,
        ]);

        // 3. Delete Page
        $deleteResp = $this->actingAs($this->portalUser)->delete(route('portal.store.pages.destroy', $page->id));
        $deleteResp->assertRedirect();
        $this->assertDatabaseMissing('cms_pages', ['id' => $page->id]);
    }

    public function test_cms_page_renders_on_storefront_with_seo_and_zero_emojis(): void
    {
        $page = CmsPage::create([
            'title' => 'Guía de Cuidados de Seda y Modal',
            'slug' => 'guia-cuidados-seda',
            'icon' => 'sparkles',
            'content_html' => '<h2>Instrucciones Oficiales de Lavado</h2><p>Lavar con agua fría a mano y secar a la sombra.</p>',
            'show_in_navbar' => true,
            'navbar_order' => 1,
            'seo_title' => 'Cuidados de Seda | Torzo Tienda',
            'seo_description' => 'Guía paso a paso para cuidar tus prendas de seda.',
            'status' => 'active',
        ]);

        $response = $this->get('/pagina/' . $page->slug);
        $response->assertOk();
        $response->assertSee('Guía de Cuidados de Seda y Modal');
        $response->assertSee('Instrucciones Oficiales de Lavado');
        $response->assertSee('Cuidados de Seda | Torzo Tienda');

        // Zero emojis check
        $content = $response->getContent();
        preg_match_all('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content, $matches);
        $this->assertCount(0, $matches[0], 'La página CMS no debe contener emojis.');
    }

    public function test_admin_can_create_update_and_delete_blog_post(): void
    {
        // 1. Create Post
        $createResp = $this->actingAs($this->portalUser)->post(route('portal.store.blog.store'), [
            'title' => 'Innovación en Binders Textiles Médicos',
            'slug' => 'innovacion-en-binders-textiles-medicos',
            'category_name' => 'Tecnología Textil',
            'tags' => ['binders', 'afirmación', 'ergonomía'],
            'author_name' => 'Dra. Elena Ramos',
            'reading_time_minutes' => 5,
            'excerpt' => 'Análisis de la dispersión de presión torácica en binders de grado médico.',
            'body' => '<p>Los binders de nueva generación utilizan mallas elásticas inteligentes para prevenir la hipoxia muscular.</p>',
            'status' => 'published',
        ]);

        $createResp->assertRedirect();
        $this->assertDatabaseHas('cms_blog_posts', [
            'slug' => 'innovacion-en-binders-textiles-medicos',
            'category_name' => 'Tecnología Textil',
        ]);

        $post = CmsBlogPost::where('slug', 'innovacion-en-binders-textiles-medicos')->firstOrFail();

        // 2. Update Post
        $updateResp = $this->actingAs($this->portalUser)->put(route('portal.store.blog.update', $post->id), [
            'title' => 'Innovación en Binders Textiles Médicos (Edición 2026)',
            'slug' => 'innovacion-en-binders-textiles-medicos',
            'category_name' => 'Innovación Médica',
            'tags' => ['binders', 'afirmación', 'actualizado'],
            'author_name' => 'Dra. Elena Ramos',
            'reading_time_minutes' => 6,
            'excerpt' => 'Resumen actualizado.',
            'body' => '<p>Cuerpo actualizado con datos clínicos de 2026.</p>',
            'status' => 'published',
        ]);

        $updateResp->assertRedirect();
        $this->assertDatabaseHas('cms_blog_posts', [
            'id' => $post->id,
            'category_name' => 'Innovación Médica',
        ]);

        // 3. Delete Post
        $delResp = $this->actingAs($this->portalUser)->delete(route('portal.store.blog.destroy', $post->id));
        $delResp->assertRedirect();
        $this->assertDatabaseMissing('cms_blog_posts', ['id' => $post->id]);
    }

    public function test_magazine_index_and_article_detail_render_on_storefront(): void
    {
        $post = CmsBlogPost::create([
            'title' => 'Secretos del Modal Pima Peruano en Ropa Interior',
            'slug' => 'secretos-del-modal-pima-peruano',
            'category_name' => 'Materiales Nobles',
            'tags' => ['modal', 'confort', 'lujo'],
            'author_name' => 'Equipo Editorial',
            'reading_time_minutes' => 4,
            'excerpt' => 'Por qué la microfibra de modal supera con creces al algodón común en prendas íntimas.',
            'body' => '<p>El modal pima conserva el brillo y suavidad incluso tras 50 ciclos de lavado industrial.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // 1. Revista Index redirige limpiamente a Home (Home Modular es la Revista Oficial)
        $indexResp = $this->get('/revista');
        $indexResp->assertRedirect(route('store.home'));

        // 2. Article Detail redirige limpiamente a Home
        $detailResp = $this->get('/revista/' . $post->slug);
        $detailResp->assertRedirect(route('store.home'));
    }

    public function test_admin_can_update_tracking_settings(): void
    {
        $response = $this->actingAs($this->portalUser)->post(route('portal.store.tracking.update'), [
            'gtm_id' => 'GTM-TEST1234',
            'ga4_measurement_id' => 'G-ABC998877',
            'facebook_pixel_id' => '9876543210',
            'facebook_capi_token' => 'EAABbCcDd123',
            'facebook_test_event_code' => 'TEST456',
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cms_tracking_settings', [
            'gtm_id' => 'GTM-TEST1234',
            'ga4_measurement_id' => 'G-ABC998877',
            'facebook_pixel_id' => '9876543210',
            'is_active' => true,
        ]);

        // Verify tracking tags render on Storefront
        $storeResp = $this->get('/');
        $storeResp->assertOk();
        $storeResp->assertSee('GTM-TEST1234');
        $storeResp->assertSee('G-ABC998877');
        $storeResp->assertSee('9876543210');
    }

    public function test_admin_can_update_catalog_filters(): void
    {
        $filter = CmsCatalogFilter::create([
            'filter_key' => 'universe',
            'label' => 'Universo Multiverso',
            'display_type' => 'pills',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        $response = $this->actingAs($this->portalUser)->post(route('portal.store.filters.update'), [
            'filters' => [
                $filter->id => [
                    'label' => 'Multiverso Universo Oficial',
                    'sort_order' => 2,
                    'is_visible' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cms_catalog_filters', [
            'id' => $filter->id,
            'label' => 'Multiverso Universo Oficial',
            'sort_order' => 2,
            'is_visible' => false,
        ]);
    }
}
