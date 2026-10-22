<?php

namespace Tests\Feature;

use App\Models\CmsCatalogFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogFilterAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_dimension_exists_and_can_be_managed_in_hub2_filters_editor()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Verificar existencia inicial o creación de la dimensión availability
        $filter = CmsCatalogFilter::firstOrCreate(
            ['filter_key' => 'availability'],
            [
                'label' => 'Disponibilidad (Stock / Bajo Pedido)',
                'display_type' => 'checkbox',
                'sort_order' => 15,
                'is_visible' => true,
            ]
        );

        $this->assertDatabaseHas('cms_catalog_filters', [
            'filter_key' => 'availability',
            'is_visible' => true,
        ]);

        // 2. Administrador actualiza la configuración del filtro desde el portal
        $response = $this->actingAs($admin)->post(route('portal.store.filters.update'), [
            'filters' => [
                $filter->id => [
                    'label' => 'Disponibilidad Inmediata',
                    'display_type' => 'checkbox',
                    'sort_order' => 1,
                    'is_visible' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cms_catalog_filters', [
            'id' => $filter->id,
            'label' => 'Disponibilidad Inmediata',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }

    public function test_storefront_renders_first_filter_open_and_subsequent_filters_collapsed()
    {
        // Configurar 3 filtros ordenados
        CmsCatalogFilter::updateOrCreate(['filter_key' => 'category'], [
            'label' => 'Categoría',
            'display_type' => 'checkbox',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        CmsCatalogFilter::updateOrCreate(['filter_key' => 'brand'], [
            'label' => 'Colección / Marca',
            'display_type' => 'checkbox',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        CmsCatalogFilter::updateOrCreate(['filter_key' => 'availability'], [
            'label' => 'Disponibilidad',
            'display_type' => 'checkbox',
            'sort_order' => 3,
            'is_visible' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Verificar que el filtro de disponibilidad está presente con En Stock y Bajo Pedido
        $response->assertSee('name="filter_availability"', false);
        $response->assertSee('value="in_stock"', false);
        $response->assertSee('value="on_demand"', false);
        $response->assertSee('En Stock', false);
        $response->assertSee('Bajo Pedido', false);

        // Verificar acordeones: el primer bloque visible no debe tener is-collapsed, los siguientes sí
        $this->assertStringContainsString('is-collapsed', $content);
    }

    public function test_price_filter_renders_as_single_line_range_slider()
    {
        CmsCatalogFilter::updateOrCreate(['filter_key' => 'price'], [
            'label' => 'Rango de Precio',
            'display_type' => 'checkbox',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Verificar el ajustador deslizador en una sola línea
        $response->assertSee('filter-price-slider-line', false);
        $response->assertSee('id="storefrontPriceSlider"', false);
        $response->assertSee('id="priceSliderValueDisplay"', false);
        $response->assertSee('$0', false);
    }
}
