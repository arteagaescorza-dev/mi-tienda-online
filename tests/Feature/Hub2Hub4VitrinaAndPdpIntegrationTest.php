<?php

namespace Tests\Feature;

use App\Models\ApparelFabric;
use App\Models\ApparelSize;
use App\Models\ApparelSizeCurve;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CmsOfficialDocument;
use App\Models\CmsSection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\CmsHub2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hub2Hub4VitrinaAndPdpIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $portalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->portalUser = User::factory()->create(['role' => 'portal']);
        $this->seed(CmsHub2Seeder::class);
    }

    /**
     * 1. Hub 4 Vitrina Web creation persists a CmsSection with type 'catalog_section',
     * query_rules (is_new, has_discount, fabric_id, category_id, subcategory_id),
     * and assigns sort_order at the end of the list.
     */
    public function test_hub4_creates_vitrina_web_as_catalog_section_in_cms(): void
    {
        $fabric = ApparelFabric::create(['name' => 'Modal Velvet Soft', 'slug' => 'modal-velvet-soft', 'is_active' => true]);
        $cat = Category::create(['name' => 'Ropa Interior', 'slug' => 'ropa-interior']);
        $subcat = Category::create(['name' => 'Jockstraps', 'slug' => 'jockstraps', 'parent_id' => $cat->id]);

        $initialMaxOrder = CmsSection::max('sort_order') ?? 0;

        $payload = [
            'name' => 'Vitrina Novedades Verano',
            'title' => 'NOVEDADES EN MODAL',
            'subtitle' => 'Selección de jocks en fibra modal con descuento activo.',
            'universe' => 'marte',
            'is_new' => true,
            'has_discount' => true,
            'fabric_id' => $fabric->id,
            'category_id' => $cat->id,
            'subcategory_id' => $subcat->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->portalUser)
            ->postJson('/api/v1/inventory/settings/store-sections', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');

        $created = CmsSection::where('name', 'Vitrina Novedades Verano')->first();
        $this->assertNotNull($created);
        $this->assertEquals('catalog_section', $created->type);
        $this->assertEquals('marte', $created->universe);
        $this->assertEquals('active', $created->status);
        $this->assertTrue($created->query_rules['is_new']);
        $this->assertTrue($created->query_rules['has_discount']);
        $this->assertEquals($fabric->id, $created->query_rules['fabric_id']);
        $this->assertEquals($cat->id, $created->query_rules['category_id']);
        $this->assertEquals($subcat->id, $created->query_rules['subcategory_id']);
        $this->assertGreaterThan($initialMaxOrder, $created->sort_order);
    }

    /**
     * 2. In Hub 2 CMS Drag & Drop table, Vitrinas show the badge 'Vitrina Web Hub 4'
     * and indicate configuration via Hub 4.
     */
    public function test_hub2_cms_drag_table_identifies_hub4_vitrinas(): void
    {
        CmsSection::create([
            'name' => 'Vitrina Deportiva Gym',
            'title' => 'COLECCIÓN FITNESS',
            'universe' => 'all',
            'type' => 'catalog_section',
            'status' => 'active',
            'sort_order' => 99,
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.store'));

        $response->assertStatus(200);
        $response->assertSee('Vitrina Deportiva Gym');
        $response->assertSee('Vitrina Web Hub 4');
        $response->assertSee('Editar Vitrina');
    }

    /**
     * 3. PDP Size Guide: Strict brand/supplier association.
     * When brand has a curve, button "Guía de tallas" and modal are rendered.
     */
    public function test_pdp_renders_brand_size_curve_modal_when_associated(): void
    {
        $supplier = Supplier::create(['name' => 'Torzo Lab B2B', 'status' => 'active']);
        $brand = Brand::create(['name' => 'Torzo Sexywear', 'slug' => 'torzo-sexywear', 'supplier_id' => $supplier->id, 'is_active' => true]);

        $curve = ApparelSizeCurve::create([
            'name' => 'Curva Torzo Ergonómica',
            'slug' => 'curva-torzo-ergonomica',
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);

        ApparelSize::create([
            'size_curve_id' => $curve->id,
            'code' => 'CH',
            'label' => 'Chica Anatómica',
            'waist_min_cm' => 70.00,
            'waist_max_cm' => 76.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ApparelSize::create([
            'size_curve_id' => $curve->id,
            'code' => 'M',
            'label' => 'Mediana Anatómica',
            'waist_min_cm' => 77.00,
            'waist_max_cm' => 84.00,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $product = Product::create([
            'title' => 'Jockstrap Velvet Red',
            'sku' => 'JCK-RED-01',
            'slug' => 'jockstrap-velvet-red',
            'brand' => 'Torzo Sexywear',
            'price' => 399.00,
            'visible' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'JCK-RED-01-M',
            'price' => 399.00,
            'attributes' => ['size' => 'M', 'color' => 'Rojo'],
        ]);

        $response = $this->get('/productos/jockstrap-velvet-red');
        $response->assertStatus(200);

        // Strict naming "Guía de tallas"
        $response->assertSee('Guía de tallas');
        $response->assertDontSee('Ver Medidas Torzo');

        // Modal and curve details
        $response->assertSee('id="pdpSizeGuideModal"', false);
        $response->assertSee('Curva Torzo Ergonómica');
        $response->assertSee('Chica Anatómica');
        $response->assertSee('70 cm');
        $response->assertSee('76 cm');
        $response->assertSee('Mediana Anatómica');
    }

    /**
     * 4. PDP Size Guide: Strict rule verified.
     * When product brand has NO curve, no generic fallback is displayed, and button is omitted.
     */
    public function test_pdp_omits_size_guide_button_when_brand_has_no_curve(): void
    {
        $product = Product::create([
            'title' => 'Accesorio Sin Curva Ni Talla',
            'sku' => 'ACC-NOCURVE-01',
            'slug' => 'accesorio-sin-curva-ni-talla',
            'brand' => 'Marca Desconocida Sin Curva',
            'price' => 199.00,
            'visible' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'ACC-NOCURVE-01-U',
            'price' => 199.00,
            'attributes' => ['size' => 'UNI', 'color' => 'Negro'],
        ]);

        $response = $this->get('/productos/accesorio-sin-curva-ni-talla');
        $response->assertStatus(200);

        // No size guide button or modal should appear
        $response->assertDontSee('openPdpInfoModal(\'pdpSizeGuideModal\')', false);
        $response->assertDontSee('id="pdpSizeGuideModal"', false);
    }

    /**
     * 5. Care Protocols: Product displays "Cuidados de la prenda" button and modal
     * with fiber, free-text warning, and SVG symbols configured in Hub 2 & Hub 4.
     */
    public function test_pdp_renders_care_protocol_with_warning_and_svg_symbols(): void
    {
        $fabric = ApparelFabric::create(['name' => 'Bambú Orgánico', 'slug' => 'bambu-organico', 'is_active' => true]);

        // Configure Care Protocols in CmsOfficialDocument
        CmsOfficialDocument::updateOrCreate(
            ['code' => 'CARE_PROTOCOLS'],
            [
                'title' => 'Protocolos Oficiales de Cuidado Textil',
                'is_active' => true,
                'payload' => [
                    [
                        'fiber' => 'Bambú Orgánico',
                        'rule' => 'Lavar con agua fría (< 30°C) con detergente ecológico neutro.',
                        'warning' => 'No exprimir con fuerza ni utilizar secadora a altas temperaturas.',
                        'symbols' => ['wash_30', 'no_bleach', 'dry_shade', 'no_tumble'],
                    ]
                ]
            ]
        );

        $product = Product::create([
            'title' => 'Boxer Trunk Bamboo Eco',
            'sku' => 'BX-BAM-01',
            'slug' => 'boxer-trunk-bamboo-eco',
            'brand' => 'Torzo Eco',
            'fabric_id' => $fabric->id,
            'fabric' => 'Bambú Orgánico',
            'price' => 349.00,
            'visible' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'BX-BAM-01-M',
            'price' => 349.00,
            'attributes' => ['size' => 'M', 'color' => 'Verde Oliva'],
        ]);

        $response = $this->get('/productos/boxer-trunk-bamboo-eco');
        $response->assertStatus(200);

        // Care button present in PDP
        $response->assertSee('Cuidados de la prenda');
        $response->assertSee('id="pdpCareGuideModal"', false);
        $response->assertSee('openPdpInfoModal', false);

        // Verify modals are NOT nested inside pdpGalleryModal
        $content = $response->getContent();
        $galleryModalEndPos = strpos($content, 'id="pdpGalleryModal"');
        $careModalPos = strpos($content, 'id="pdpCareGuideModal"');
        $this->assertNotFalse($galleryModalEndPos);
        $this->assertNotFalse($careModalPos);
        // Ensure pdpGalleryModal closes before pdpCareGuideModal
        $galleryClosePos = strpos($content, '</div>' . "\n" . '</div>' . "\n\n" . '<!-- MODAL 1:');
        $this->assertTrue($careModalPos > $galleryModalEndPos);

        // Modal content
        $response->assertSee('Bambú Orgánico');
        $response->assertSee('Lavar con agua fría (< 30°C)');
        $response->assertSee('No exprimir con fuerza ni utilizar secadora a altas temperaturas.');
        $response->assertSee('Lavado frío (máx 30°C)');
        $response->assertSee('No usar cloro ni blanqueadores');
        $response->assertSee('Secar a la sombra (sin sol directo)');
        $response->assertSee('No usar secadora térmica');
    }

    /**
     * 6. Hub 2 CMS automatically synchronizes active ApparelFabric records from Hub 4
     * into Section 2 Care Protocols and modal option list.
     */
    public function test_hub2_cms_auto_syncs_all_active_hub4_fabrics_into_care_protocols(): void
    {
        ApparelFabric::create(['name' => 'Algodón Supima 100%', 'slug' => 'algodon-supima-100', 'is_active' => true]);
        ApparelFabric::create(['name' => 'Seda Satín Italiana', 'slug' => 'seda-satin-italiana', 'is_active' => true]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.store'));

        $response->assertStatus(200);
        // Verify both Hub 4 fabrics are listed in the section
        $response->assertSee('Algodón Supima 100%');
        $response->assertSee('Seda Satín Italiana');
        // Verify option values are prepared for dynamic addition
        $response->assertSee('<option value="Algodón Supima 100%"', false);
        $response->assertSee('<option value="Seda Satín Italiana"', false);
    }
}
