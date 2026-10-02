<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndProjectRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_rule_2_cms_page_filter_supplier_attribute_and_persistence(): void
    {
        $cmsPage = CmsPage::create([
            'slug' => 'explore-silk-cotton',
            'title' => 'Colección Seda y Algodón',
            'type' => 'explore',
            'content' => 'Productos seleccionados',
            'filter_supplier' => 'Silk & Cotton Iberia SL',
            'is_published' => true,
        ]);

        $this->assertDatabaseHas('cms_pages', [
            'id' => $cmsPage->id,
            'filter_supplier' => 'Silk & Cotton Iberia SL',
        ]);

        $this->assertEquals('Silk & Cotton Iberia SL', $cmsPage->fresh()->filter_supplier);
    }

    public function test_rule_2_and_3_storefront_provides_categories_tree_and_suppliers_with_tree_accordions(): void
    {
        Supplier::create([
            'name' => 'Silk & Cotton Iberia SL',
            'vendor_code' => 'SC-IB',
            'type' => 'wholesale',
            'status' => 'connected',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        // Debe contener el acordeón de árbol jerárquico de categorías
        $response->assertSee('filter-accordion-block', false);
        $response->assertSee('filter-tree-container', false);
        // Debe contener faceta de proveedores con checkboxes
        $response->assertSee('name="filter_supplier"', false);
        // Debe contener facetas de telas, colores, tallas
        $response->assertSee('name="filter_fabric"', false);
        $response->assertSee('name="filter_color"', false);
    }

    public function test_rule_4_product_detail_page_has_back_button_and_filter_toggle_button(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-boxer-seamless',
            'title' => 'Boxer Seamless Premium',
            'visible' => true,
            'price' => 399.00,
        ]);

        $response = $this->get('/productos/' . $product->slug);

        $response->assertStatus(200);
        // Regla 4: Botón volver atrás
        $response->assertSee('window.history.back()', false);
        // Regla 4: Botón que conmuta a filtros
        $response->assertSee('data-action="open-filters"', false);
    }

    public function test_rule_5_and_6_pdp_includes_color_swatches_and_color_switching_logic(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-jock-iridescent',
            'title' => 'Jockstrap Iridiscente',
            'visible' => true,
            'price' => 450.00,
        ]);

        $response = $this->get('/productos/' . $product->slug);

        $response->assertStatus(200);
        // Regla 5: Exhibidor de color indexado
        $response->assertSee('pdp-color-swatches-group', false);
        // Regla 6: Función de conmutación de fotos al hacer click en variantes de color
        $response->assertSee('selectPdpColor', false);
    }

    public function test_rule_7_and_8_cart_and_checkout_coupon_separation(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Regla 8: El drawer del carrito NO debe tener cartCouponBox
        $response->assertDontSee('id="cartCouponBox"', false);
        // Regla 8: El checkout paso 1 sí mantiene el cupón
        $response->assertSee('checkoutCouponBox', false);
    }

    public function test_rule_9_and_10_inventory_blade_contains_image_thumbnails_and_curation_color_mapping(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('portal.inventory'));

        $response->assertStatus(200);
        // Regla 9: Columna imagen y preview en drawer
        $response->assertSee('x-model="drawerImage"', false);
        $response->assertSee('drawerImageColorMap', false);
        // Regla 10: Modal de curaduría de scraping con asignación de fotos a variantes de color
        $response->assertSee('Curaduría de Fotografías & Variante de Color', false);
        $response->assertSee('curatingItem.imageColorMap', false);
    }
}
