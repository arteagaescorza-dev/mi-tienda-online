<?php

namespace Tests\Feature;

use App\Models\AllyCampaignDirA;
use App\Models\CmsPage;
use App\Models\DirectorioA;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPageCatalogRedirectionAndAlliesTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_with_category_filter_identifies_catalog_filter_and_redirects()
    {
        $page = CmsPage::create([
            'slug' => 'tanga',
            'title' => 'Tanga Ergonómica',
            'filter_category' => 'tanga',
            'filter_universe' => 'all',
            'image_source_mode' => 'catalog',
            'status' => 'active',
        ]);

        $this->assertTrue($page->hasCatalogFilters());
        $this->assertEquals('tanga', $page->getEffectiveCategory());
        
        $params = $page->getCatalogParams('marte');
        $this->assertEquals('catalog', $params['tab']);
        $this->assertEquals('tanga', $params['categoria']);

        $response = $this->get('/pagina/tanga');
        $response->assertRedirect(route('store.home', $params));
    }

    public function test_page_with_inferred_category_redirects_to_catalog()
    {
        $page = CmsPage::create([
            'slug' => 'boxers-premium',
            'title' => 'Colección Boxers',
            'filter_category' => null,
            'filter_universe' => 'marte',
            'image_source_mode' => 'catalog',
            'status' => 'active',
        ]);

        $this->assertTrue($page->hasCatalogFilters());
        $this->assertEquals('boxer', $page->getEffectiveCategory());

        $response = $this->get('/pagina/boxers-premium');
        $response->assertRedirect(route('store.home', [
            'tab' => 'catalog',
            'categoria' => 'boxer',
            'universo' => 'marte',
        ]));
    }

    public function test_catalogo_route_redirects_to_home_with_tab_and_query_params()
    {
        $response = $this->get('/catalogo?categoria=tanga&universo=marte');
        $response->assertRedirect(route('store.home', [
            'categoria' => 'tanga',
            'universo' => 'marte',
            'tab' => 'catalog',
        ]));
    }

    public function test_aliados_page_shows_events_repository_and_prevents_manual_mutation()
    {
        $ally = \App\Models\Ally::create([
            'code' => 'ALLY-PARADISO',
            'name' => 'Club Paradiso',
            'category' => 'circuit_party',
            'portal_token' => 'token_paradiso_123',
            'contact_name' => 'Gerente Paradiso',
            'contact_email' => 'paradiso@example.com',
            'status' => 'active',
        ]);

        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => 'Noche de Gala y Pasarela Torzo',
            'slug' => 'noche-gala-torzo',
            'status' => 'active',
            'benefit' => '2x1 en Taquilla Oficial',
            'banner_image' => 'https://via.placeholder.com/800x400.jpg',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
        ]);

        $page = CmsPage::create([
            'slug' => 'aliados',
            'title' => 'Convenios con aliados',
            'content_html' => '<p>Original</p>',
            'status' => 'active',
        ]);

        // Accessing /pagina/aliados renders the automated repository
        $response = $this->get('/pagina/aliados');
        $response->assertStatus(200);
        $response->assertSee('REPOSITORIO OFICIAL');
        $response->assertSee('Noche de Gala y Pasarela Torzo');
        $response->assertSee('Club Paradiso');

        // Admin attempting manual update is blocked
        $admin = User::factory()->create(['role' => 'admin']);
        $updateResp = $this->actingAs($admin)->put(route('portal.store.pages.update', $page->id), [
            'title' => 'Intento Modificar Manualmente',
            'content' => 'Texto nuevo',
        ]);
        $updateResp->assertSessionHas('error');
    }
}
