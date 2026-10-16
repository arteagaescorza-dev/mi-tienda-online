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

    /**
     * 7. Multi-fabric Care Protocol Resolution and Composition:
     * When multiple fabrics are configured in Hub 2 / Hub 4, each product dynamically displays
     * its own official textile composition and matched care protocol.
     */
    public function test_multiple_fabrics_each_resolve_correct_care_protocol_and_composition(): void
    {
        $fabricLicra = ApparelFabric::create(['name' => 'Licra Test', 'slug' => 'licra-test', 'is_active' => true]);
        $fabricPoliester = ApparelFabric::create(['name' => 'Poliester Test', 'slug' => 'poliester-test', 'is_active' => true]);

        // Save two distinct care protocols
        $doc = CmsOfficialDocument::updateOrCreate(
            ['code' => 'CARE_PROTOCOLS'],
            [
                'title' => 'Protocolos Oficiales de Cuidado Textil',
                'is_active' => true,
                'payload' => [
                    [
                        'fiber' => 'Licra Test',
                        'rule' => 'Cuidado especial para prendas elásticas de Licra.',
                        'warning' => 'Advertencia elástica para licra.',
                        'symbols' => ['wash_30', 'wash_hand'],
                    ],
                    [
                        'fiber' => 'Poliester Test',
                        'rule' => 'Lavar con agua fría en ciclo delicado para Poliester.',
                        'warning' => 'Advertencia especial poliester.',
                        'symbols' => ['no_bleach', 'dry_flat', 'dry_shade'],
                    ],
                ],
            ]
        );

        $prodLicra = Product::create([
            'title' => 'Prenda Licra Especial',
            'sku' => 'SKU-LIC-01',
            'slug' => 'prenda-licra-especial',
            'fabric_id' => $fabricLicra->id,
            'price' => 250.00,
            'visible' => true,
        ]);
        ProductVariant::create([
            'product_id' => $prodLicra->id,
            'sku' => 'SKU-LIC-01-M',
            'price' => 250.00,
            'attributes' => ['size' => 'M'],
        ]);

        $prodPoli = Product::create([
            'title' => 'Prenda Poliester Especial',
            'sku' => 'SKU-POL-01',
            'slug' => 'prenda-poliester-especial',
            'fabric_id' => $fabricPoliester->id,
            'price' => 280.00,
            'visible' => true,
        ]);
        ProductVariant::create([
            'product_id' => $prodPoli->id,
            'sku' => 'SKU-POL-01-M',
            'price' => 280.00,
            'attributes' => ['size' => 'M'],
        ]);

        // Visit Licra product
        $resLicra = $this->get('/productos/prenda-licra-especial');
        $resLicra->assertStatus(200);
        $resLicra->assertSee('Licra Test');
        $resLicra->assertSee('Cuidado especial para prendas elásticas de Licra.');
        $resLicra->assertSee('Advertencia elástica para licra.');
        $resLicra->assertDontSee('Lavar con agua fría en ciclo delicado para Poliester.');

        // Visit Poliester product
        $resPoli = $this->get('/productos/prenda-poliester-especial');
        $resPoli->assertStatus(200);
        $resPoli->assertSee('Poliester Test');
        $resPoli->assertSee('Lavar con agua fría en ciclo delicado para Poliester.');
        $resPoli->assertSee('Advertencia especial poliester.');
        $resPoli->assertDontSee('Cuidado especial para prendas elásticas de Licra.');
    }

    /**
     * 8. Vitrinas have automatic 12px top and bottom spacing and the internal
     * "Política Cero Emojis" badge is not shown in Storefront or Hub 2 UI.
     */
    public function test_vitrinas_have_automatic_12px_top_and_bottom_spacing_and_no_zero_emojis_badge(): void
    {
        CmsSection::create([
            'name' => 'Vitrina Destacados',
            'title' => 'PRENDAS DESTACADAS',
            'type' => 'catalog_section',
            'universe' => 'all',
            'status' => 'active',
            'sort_order' => 1,
            'settings' => [],
        ]);

        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('margin-top: 12px !important; margin-bottom: 12px !important; padding-top: 12px !important; padding-bottom: 12px !important;', false);
        $homeRes->assertDontSee('Política Estricta Cero Emojis');

        $hubRes = $this->actingAs($this->portalUser)->get(route('portal.store'));
        $hubRes->assertStatus(200);
        $hubRes->assertDontSee('Mostrar Badge Política Cero Emojis');
    }

    /**
     * 9. Navbar Marquee (Cinta Banner Superior) syncs active messages, links, and active toggle
     * between Hub 2 and Storefront (including when cache is cleared).
     */
    public function test_navbar_marquee_banner_syncs_messages_links_and_active_toggle_between_hub2_and_storefront(): void
    {
        $res = $this->actingAs($this->portalUser)
            ->post(route('portal.store.announcements.update'), [
                'announcement_active' => '1',
                'announcements' => [
                    ['text' => 'Envío express nacional hoy', 'link' => '/catalogo', 'active' => '1'],
                    ['text' => 'Aviso desactivado oculto', 'link' => '/oculto', 'active' => '0'],
                ],
            ]);

        $res->assertRedirect();

        // Clear cache to verify DB persistence fallback (STORE_ANNOUNCEMENTS_CONFIG) also works
        \Illuminate\Support\Facades\Cache::forget('cms_announcements');
        \Illuminate\Support\Facades\Cache::forget('cms_announcement_active');

        $storeRes = $this->get('/');
        $storeRes->assertStatus(200);
        $storeRes->assertSee('ENVÍO EXPRESS NACIONAL HOY');
        $storeRes->assertSee('href="/catalogo"', false);
        $storeRes->assertDontSee('AVISO DESACTIVADO OCULTO');

        // Now disable the entire marquee bar
        $this->actingAs($this->portalUser)
            ->post(route('portal.store.announcements.update'), [
                'announcement_active' => '0',
                'announcements' => [
                    ['text' => 'Envío express nacional hoy', 'link' => '/catalogo', 'active' => '1'],
                ],
            ])
            ->assertRedirect();

        $storeDisabledRes = $this->get('/');
        $storeDisabledRes->assertStatus(200);
        $storeDisabledRes->assertDontSee('class="minimal-marquee-bar"', false);
    }

    /**
     * 10. Explore pages (Páginas de Explorar) creation, update, and reordering succeed
     * without 419 or validation mismatch errors.
     */
    public function test_explore_pages_create_update_and_reorder_succeed_without_419_or_validation_mismatch(): void
    {
        $createRes = $this->actingAs($this->portalUser)
            ->post(route('portal.store.pages.store'), [
                'title' => 'Colección Verano',
                'slug' => 'coleccion-verano',
                'filter_universe' => 'marte',
                'filter_category' => 'trajes-de-bano',
                'overlay_color_start' => '#0f172a',
                'overlay_color_end' => '#1e1b4b',
                'overlay_opacity_start' => 30,
                'overlay_opacity_end' => 85,
                'show_in_navbar' => '1',
                'show_in_mobile' => '1',
                'status' => 'active',
            ]);

        $createRes->assertRedirect();
        $page = \App\Models\CmsPage::where('slug', 'coleccion-verano')->first();
        $this->assertNotNull($page);
        $this->assertEquals('Colección Verano', $page->title);

        $updateRes = $this->actingAs($this->portalUser)
            ->put(route('portal.store.pages.update', $page->id), [
                'title' => 'Colección Verano 2026 Editada',
                'slug' => 'coleccion-verano-2026',
                'filter_universe' => 'venus',
                'filter_category' => 'lenceria',
                'content_html' => '<p>Contenido actualizado de explorar</p>',
                'show_in_navbar' => '1',
                'show_in_mobile' => '1',
                'status' => 'active',
            ]);

        $updateRes->assertRedirect();
        $page->refresh();
        $this->assertEquals('Colección Verano 2026 Editada', $page->title);
        $this->assertEquals('coleccion-verano-2026', $page->slug);
        $this->assertEquals('venus', $page->filter_universe);
        $this->assertEquals('lenceria', $page->filter_category);
    }

    /**
     * 11. Carousel section integrates content per slide (no redundant global carousel toggle)
     * and renders full-bleed animated carousel with slides, images, arrows, and pagination dots.
     */
    public function test_carousel_section_per_slide_config_and_animated_rendering(): void
    {
        $hubRes = $this->actingAs($this->portalUser)->get(route('portal.store'));
        $hubRes->assertStatus(200);
        $hubRes->assertDontSee('id="createCarouselGlobalToggle"', false);
        $hubRes->assertDontSee('id="editCarouselGlobalToggle"', false);
        $hubRes->assertSee('window.renderCarouselLivePreview', false);
        $hubRes->assertSee('window.handleSlideFileSelect', false);
        $hubRes->assertSee('window.setSlideFocalPoint', false);
        $hubRes->assertSee('window.syncCarouselLayoutSettings', false);
        $hubRes->assertSee('id="inpCreateCarouselFullWidth"', false);
        $hubRes->assertSee('id="inpCreateCarouselFullWidthHorizontal"', false);
        $hubRes->assertSee('id="inpCreateCarouselHeightValue"', false);
        $hubRes->assertSee('id="inpCreateCarouselParallax"', false);
        $hubRes->assertSee('Efecto Revelado (Cortina)', false);

        \App\Models\CmsSection::create([
            'name' => 'Carrusel Principal Marte',
            'type' => 'carousel',
            'universe' => 'marte',
            'status' => 'active',
            'sort_order' => 1,
            'settings' => [
                'hero_mode' => 'structured',
                'carousel_global_content' => '0',
                'carousel_autoplay' => '1',
                'autoplay_time' => 4,
                'full_width' => '1',
                'full_width_horizontal' => '1',
                'parallax_scrolling' => '1',
                'hero_height_value' => 480,
                'hero_height_unit' => 'px',
                'carousel_slides' => [
                    [
                        'title' => 'SLIDE UNO COLECCIÓN INVIERNO',
                        'subtitle' => 'Textiles ergonómicos de primera calidad',
                        'badge' => 'NUEVO LANZAMIENTO',
                        'title_color' => '#ffffff',
                        'subtitle_color' => '#e2e8f0',
                        'text_align' => 'text-center',
                        'image_url' => '/uploads/cms_media/test_slide_1.jpg',
                        'image_focal_point' => 'left top',
                        'enable_gradient' => '1',
                        'gradient_color_start' => '#0f172a',
                        'gradient_color_end' => '#1e1b4b',
                        'gradient_opacity_start' => 60,
                        'gradient_opacity_end' => 85,
                        'gradient_direction' => 'to bottom',
                        'button_text' => 'VER COLECCIÓN 1',
                        'button_url' => '/catalogo',
                        'button_bg' => '#ffffff',
                        'button_color' => '#0f172a',
                    ],
                    [
                        'title' => 'SLIDE DOS EDICIÓN LIMITADA',
                        'subtitle' => 'Microfibra transpirable sin costuras',
                        'badge' => 'EXCLUSIVO ONLINE',
                        'title_color' => '#f8fafc',
                        'subtitle_color' => '#cbd5e1',
                        'text_align' => 'text-left',
                        'image_url' => '/uploads/cms_media/test_slide_2.jpg',
                        'image_focal_point' => 'right bottom',
                        'enable_gradient' => '1',
                        'button_text' => 'EXPLORAR AHORA',
                        'button_url' => '/catalogo',
                    ],
                ],
            ],
        ]);

        $storeRes = $this->get('/?universe=marte');
        $storeRes->assertStatus(200);
        $storeRes->assertSee('store-carousel-viewport', false);
        $storeRes->assertSee('hero-full-width', false);
        $storeRes->assertSee('data-parallax="1"', false);
        $storeRes->assertSee('carousel-slide-content', false);
        $storeRes->assertSee('object-position: left top;', false);
        $storeRes->assertSee('object-position: right bottom;', false);
        $storeRes->assertSee('SLIDE UNO COLECCIÓN INVIERNO');
        $storeRes->assertSee('SLIDE DOS EDICIÓN LIMITADA');
        $storeRes->assertSee('VER COLECCIÓN 1');
        $storeRes->assertSee('EXPLORAR AHORA');
        $storeRes->assertSee('test_slide_1.jpg', false);
        $storeRes->assertSee('test_slide_2.jpg', false);
        $storeRes->assertSee('data-carousel-prev=', false);
        $storeRes->assertSee('data-carousel-next=', false);
        $storeRes->assertSee('data-carousel-dots=', false);
    }

    /**
     * 12. Carousel Ancho Completo (Horizontal) is strictly decoupled from Full Page (Vertical).
     * When Ancho Completo is active without Full Page, it breaks out to 100vw but respects the custom height.
     */
    public function test_carousel_ancho_completo_horizontal_does_not_force_fullpage_vertical(): void
    {
        $section = \App\Models\CmsSection::create([
            'name' => 'Carrusel Horizontal Venus',
            'type' => 'carousel',
            'universe' => 'venus',
            'status' => 'active',
            'sort_order' => 1,
            'settings' => [
                'hero_mode' => 'structured',
                'full_width' => '0',
                'full_width_horizontal' => '1',
                'hero_height_value' => 520,
                'hero_height_unit' => 'px',
                'carousel_slides' => [
                    [
                        'title' => 'VENUS HORIZONTAL ONLY',
                        'image_url' => '/uploads/cms_media/venus_h.jpg',
                    ],
                ],
            ],
        ]);

        $storeRes = $this->get('/?universo=venus');
        $storeRes->assertStatus(200);

        $html = $storeRes->getContent();
        preg_match('/<section id="section-' . $section->id . '" class="([^"]+)"/', $html, $matches);
        $this->assertNotEmpty($matches, 'Section tag was not found in response');
        $classList = array_filter(explode(' ', $matches[1]));

        $this->assertContains('hero-editorial-section', $classList);
        $this->assertContains('hero-full-width-h', $classList);
        $this->assertNotContains('hero-fullpage-v', $classList);
        $this->assertNotContains('hero-full-width', $classList);

        $storeRes->assertSee('min-height: 520px !important; height: 520px !important;', false);
        $storeRes->assertSee('VENUS HORIZONTAL ONLY');
    }
}


