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

    /**
     * Regla 6.45: Estructura de Árbol Proveedor -> Marcas desprendidas en Hub 4 Ajustes,
     * y Alta Manual completa con información de Contacto y Fiscal.
     */
    public function test_rule_6_45_supplier_tree_structure_and_manual_onboarding_with_fiscal_contact_data(): void
    {
        $payload = [
            'name' => 'Maquilas Textiles Mexicanas SA de CV',
            'tradeName' => 'Maquilas MX',
            'legalName' => 'Maquilas Textiles Mexicanas SA de CV',
            'taxId' => 'MTM990101XYZ',
            'fiscalRegime' => '601 - General de Ley Personas Morales',
            'fiscalAddress' => 'Calzada de Tlalpan 456, Col. Álamos, Benito Juárez, CDMX',
            'fiscalZip' => '03400',
            'invoiceEmail' => 'facturas@maquilasmx.com',
            'website' => 'https://maquilasmx.com',
            'contactName' => 'Lic. Roberto Garza',
            'phone' => '+52 55 5555 1234',
            'email' => 'pedidos@maquilasmx.com',
            'city' => 'CDMX',
            'origin' => 'manual',
            'type' => 'workshop',
            'leadTimeDays' => 4,
            'paymentTerms' => 'credito_30',
            'brands' => ['Seda & Encaje', 'Aura Lingerie', 'Lenceria Premium MX'],
        ];

        // 1. Alta manual completa
        $res = $this->postJson('/api/v1/inventory/suppliers', $payload);
        $res->assertStatus(201);
        $res->assertJsonPath('status', 'success');
        $res->assertJsonPath('data.taxId', 'MTM990101XYZ');
        $res->assertJsonPath('data.legalName', 'Maquilas Textiles Mexicanas SA de CV');
        $res->assertJsonPath('data.fiscalRegime', '601 - General de Ley Personas Morales');
        $res->assertJsonPath('data.fiscalZip', '03400');
        $res->assertJsonPath('data.invoiceEmail', 'facturas@maquilasmx.com');
        $res->assertJsonPath('data.isManual', true);
        $res->assertJsonPath('data.origin', 'manual');
        $res->assertJsonCount(3, 'data.brands');
        $res->assertJsonCount(3, 'data.brandsTree');
        $res->assertJsonPath('data.brandsTree.0.name', 'Seda & Encaje');
        $res->assertJsonPath('data.brandsTree.0.slug', 'seda-encaje');

        $supplierId = $res->json('data.dbId');

        // 2. Asociación de nueva marca al árbol
        $addBrandRes = $this->postJson("/api/v1/inventory/suppliers/{$supplierId}/brands", [
            'brand' => 'Nueva Marca Hija',
        ]);
        $addBrandRes->assertStatus(200);
        $addBrandRes->assertJsonCount(4, 'data.brands');
        $addBrandRes->assertJsonCount(4, 'data.brandsTree');

        // 3. Desvinculación de marca del árbol
        $removeBrandRes = $this->deleteJson("/api/v1/inventory/suppliers/{$supplierId}/brands", [
            'brand' => 'Nueva Marca Hija',
        ]);
        $removeBrandRes->assertStatus(200);
        $removeBrandRes->assertJsonCount(3, 'data.brands');

        // 4. Actualización de datos fiscales
        $updateRes = $this->putJson("/api/v1/inventory/suppliers/{$supplierId}", [
            'fiscalRegime' => '626 - Régimen Simplificado de Confianza (RESICO)',
            'taxId' => 'MTM990101ABC',
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('data.fiscalRegime', '626 - Régimen Simplificado de Confianza (RESICO)');
        $updateRes->assertJsonPath('data.taxId', 'MTM990101ABC');

        // 5. Verificación de UI en Blade
        $admin = User::factory()->create(['role' => 'admin']);
        $portalResponse = $this->actingAs($admin)->get(route('portal.inventory'));
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('Proveedor / Estructura en Árbol', false);
        $portalResponse->assertSee('Marcas Comerciales Desprendidas (Árbol Jerárquico)', false);
        $portalResponse->assertSee('Expandir Árbol', false);
        $portalResponse->assertSee('providerOriginFilter', false);
        $portalResponse->assertSee('Información Fiscal & Facturación', false);
        $portalResponse->assertSee('RFC / Tax ID / NIF', false);
        $portalResponse->assertSee('Régimen Fiscal (SAT / Tributario)', false);
        $portalResponse->assertSee('Domicilio Fiscal Completo', false);
        $portalResponse->assertSee('Desprender / Asociar Nueva Marca Comercial', false);
    }
}
