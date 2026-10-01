<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StorefrontNavbarSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_storefront_home_renders_sticky_header_marquee_and_seo_tags()
    {
        Cache::forever('cms_announcements', [
            ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'active' => true],
            ['id' => 2, 'text' => 'PAGOS 100% SEGUROS', 'active' => true],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('sticky-header-wrapper', false);
        $response->assertSee('minimal-marquee-bar', false);
        $response->assertSee('ENVÍOS EXPRESS A TODO MÉXICO');
        $response->assertSee('PAGOS 100% SEGUROS');
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:site_name"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('https://schema.org', false);
    }

    public function test_sitemap_xml_returns_valid_xml_with_proper_headers()
    {
        CmsPage::create([
            'title' => 'Términos de Garantía',
            'slug' => 'terminos-garantia',
            'content_html' => '<p>Garantía de satisfacción.</p>',
            'status' => 'active',
            'show_in_navbar' => true,
            'show_in_mobile' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('/pagina/terminos-garantia', false);
        $response->assertSee('<changefreq>', false);
        $response->assertSee('<priority>', false);
    }

    public function test_storefront_displays_desktop_and_mobile_institutional_pages()
    {
        $desktopPage = CmsPage::create([
            'title' => 'Desktop Only Policy',
            'slug' => 'desktop-policy',
            'content_html' => '<p>Desktop only.</p>',
            'status' => 'active',
            'show_in_navbar' => true,
            'show_in_mobile' => false,
            'navbar_order' => 1,
        ]);

        $mobilePage = CmsPage::create([
            'title' => 'Mobile Only Guide',
            'slug' => 'mobile-guide',
            'content_html' => '<p>Mobile only.</p>',
            'status' => 'active',
            'show_in_navbar' => false,
            'show_in_mobile' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('DESKTOP ONLY POLICY');
        $response->assertSee('MOBILE ONLY GUIDE');
        $response->assertSee('/pagina/desktop-policy');
        $response->assertSee('/pagina/mobile-guide');
    }

    public function test_admin_can_update_announcements_from_tab_pages()
    {
        $admin = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin_cms@torzo.test',
        ]);

        $payload = [
            'announcement_active' => '1',
            'announcements' => [
                ['text' => 'NUEVO DROP PRIMAVERA 2026', 'link' => '/novedades', 'active' => '1'],
                ['text' => 'ENVÍOS GRATIS EN COMPRAS MAYORES A $999', 'link' => '', 'active' => '1'],
            ],
        ];

        $response = $this->actingAs($admin)
            ->post(route('portal.store.announcements.update'), $payload);

        $response->assertRedirect();
        $cached = Cache::get('cms_announcements');
        $this->assertIsArray($cached);
        $this->assertEquals('NUEVO DROP PRIMAVERA 2026', $cached[0]['text']);
        $this->assertTrue(Cache::get('cms_announcement_active'));
    }

    public function test_admin_can_create_update_toggle_and_archive_cms_page()
    {
        $admin = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin_cms_pages@torzo.test',
        ]);

        // 1. Create with dual desktop and mobile flags
        $createResponse = $this->actingAs($admin)
            ->post(route('portal.store.pages.store'), [
                'title' => 'Aviso de Privacidad 2026',
                'slug' => 'privacidad-2026',
                'content' => '<p>Política estricta de protección de datos.</p>',
                'show_in_navbar' => '1',
                'show_in_mobile' => '1',
                'show_in_footer' => '1',
                'status' => 'active',
            ]);

        $createResponse->assertRedirect();
        $page = CmsPage::where('slug', 'privacidad-2026')->first();
        $this->assertNotNull($page);
        $this->assertTrue((bool)$page->show_in_navbar);
        $this->assertTrue((bool)$page->show_in_mobile);
        $this->assertEquals('active', $page->status);

        // 2. Update page
        $updateResponse = $this->actingAs($admin)
            ->put(route('portal.store.pages.update', $page->id), [
                'title' => 'Aviso de Privacidad Actualizado',
                'slug' => 'privacidad-2026',
                'content' => '<p>Contenido renovado.</p>',
                'show_in_navbar' => '1',
                'show_in_mobile' => '0',
                'status' => 'active',
            ]);

        $updateResponse->assertRedirect();
        $page->refresh();
        $this->assertEquals('Aviso de Privacidad Actualizado', $page->title);
        $this->assertFalse((bool)$page->show_in_mobile);

        // 3. Toggle status to paused
        $toggleResponse = $this->actingAs($admin)
            ->post(route('portal.store.pages.toggle-status', $page->id));

        $toggleResponse->assertRedirect();
        $page->refresh();
        $this->assertEquals('paused', $page->status);

        // 4. Archive page
        $archiveResponse = $this->actingAs($admin)
            ->post(route('portal.store.pages.archive', $page->id));

        $archiveResponse->assertRedirect();
        $page->refresh();
        $this->assertEquals('archived', $page->status);

        // 5. Delete page
        $deleteResponse = $this->actingAs($admin)
            ->delete(route('portal.store.pages.destroy', $page->id));

        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('cms_pages', ['id' => $page->id]);
    }
}
