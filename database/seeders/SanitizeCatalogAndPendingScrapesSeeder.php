<?php

namespace Database\Seeders;

use App\Models\ApparelFabric;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ScrapedPendingItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SanitizeCatalogAndPendingScrapesSeeder extends Seeder
{
    /**
     * Sanea el Catálogo Operativo removiendo productos que residen en curaduría previa pendiente (Regla 2),
     * e introduce productos propios locales de confección y un producto descontinuado de prueba (Reglas 4 y 5).
     */
    public function run(): void
    {
        // 1. Obtener los SKUs de los ítems que están pendientes de curación
        $pendingSkus = ScrapedPendingItem::where('curation_status', 'pending')
            ->pluck('external_sku')
            ->filter()
            ->all();

        // 2. Remover de products los productos que están pendientes de curación humana
        if (!empty($pendingSkus)) {
            $productsToRemove = Product::whereIn('sku', $pendingSkus)->get();
            foreach ($productsToRemove as $p) {
                // Borrar variantes e inventarios asociados
                foreach ($p->variants as $var) {
                    Inventory::where('variant_id', $var->id)->delete();
                    $var->delete();
                }
                $p->delete();
            }
        }

        // Si aún quedan productos viejos de Torzo no curados que tengan SKU00...
        $oldTorzoProds = Product::where('sku', 'like', 'SKU00%')->get();
        foreach ($oldTorzoProds as $p) {
            foreach ($p->variants as $var) {
                Inventory::where('variant_id', $var->id)->delete();
                $var->delete();
            }
            $p->delete();
        }

        // 3. Asegurar Telas para los productos propios
        $fabricModal = ApparelFabric::firstOrCreate(
            ['name' => 'Modal Seamless'],
            ['slug' => 'modal-seamless', 'description' => 'Tejido ultra suave de fibra de haya sin costuras', 'is_active' => true]
        );
        $fabricLicra = ApparelFabric::firstOrCreate(
            ['name' => 'Microfibra Spandex / Licra'],
            ['slug' => 'microfibra-spandex-licra', 'description' => 'Tejido elástico de alta compresión y secado rápido', 'is_active' => true]
        );

        // 4. Crear productos propios de taller local en Catálogo Operativo (Inventario propio)
        $ownProducts = [
            [
                'sku' => 'PRP-BOX-001',
                'title' => 'Boxer Modal Seamless Confección Local',
                'brand' => 'Taller Propio CDMX',
                'fabric' => 'Modal Seamless',
                'fabric_id' => $fabricModal->id,
                'section_name' => 'basicos',
                'type' => 'physical',
                'price' => 289.00,
                'cost' => 95.00,
                'visible' => true,
                'is_archived' => false,
                'meta' => [
                    'category' => 'Boxers',
                    'color_name' => 'Negro Profundo',
                    'fabric_type' => 'Modal Seamless',
                    'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600&auto=format&fit=crop&q=80',
                    'images' => [
                        'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
                'sizes' => ['ch' => 15, 'm' => 25, 'g' => 20, 'xl' => 10],
            ],
            [
                'sku' => 'PRP-TNG-002',
                'title' => 'Tanga Microfibra Athletic Confección Local',
                'brand' => 'Taller Propio CDMX',
                'fabric' => 'Microfibra Spandex / Licra',
                'fabric_id' => $fabricLicra->id,
                'section_name' => 'basicos',
                'type' => 'physical',
                'price' => 220.00,
                'cost' => 70.00,
                'visible' => true,
                'is_archived' => false,
                'meta' => [
                    'category' => 'Tanga',
                    'color_name' => 'Rojo Borgoña',
                    'fabric_type' => 'Microfibra Spandex / Licra',
                    'image' => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&auto=format&fit=crop&q=80',
                    'images' => [
                        'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
                'sizes' => ['ch' => 10, 'm' => 20, 'g' => 15, 'xl' => 5],
            ],
            [
                'sku' => 'PRP-JCK-003',
                'title' => 'Jockstrap Deportivo Resistente Taller Propio',
                'brand' => 'Taller Propio CDMX',
                'fabric' => 'Microfibra Spandex / Licra',
                'fabric_id' => $fabricLicra->id,
                'section_name' => 'temporada',
                'type' => 'physical',
                'price' => 260.00,
                'cost' => 85.00,
                'visible' => true,
                'is_archived' => false,
                'meta' => [
                    'category' => 'Jockstraps',
                    'color_name' => 'Azul Marino',
                    'fabric_type' => 'Microfibra Spandex / Licra',
                    'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600&auto=format&fit=crop&q=80',
                    'images' => [
                        'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
                'sizes' => ['ch' => 8, 'm' => 16, 'g' => 12, 'xl' => 4],
            ],
        ];

        foreach ($ownProducts as $pData) {
            $sizes = $pData['sizes'];
            unset($pData['sizes']);

            $product = Product::create(array_merge($pData, [
                'slug' => Str::slug($pData['title']),
            ]));

            foreach ($sizes as $sz => $qty) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $product->sku . '-' . strtoupper($sz),
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'attributes' => ['size' => strtoupper($sz)],
                ]);

                Inventory::create([
                    'variant_id' => $variant->id,
                    'quantity' => $qty,
                    'reserved' => 0,
                    'location' => 'Rack A-01 / Nivel 1',
                    'min_threshold' => 3,
                ]);
            }
        }

        // 5. Crear 1 Producto Descontinuado de Prueba en la herramienta de Archivados (Regla 4 y 5)
        $discontinuedProduct = Product::create([
            'sku' => 'TOR-DISC-DEMO',
            'title' => 'Bikini Microfibra Torzo (Retirado en Origen)',
            'brand' => 'Torzo',
            'fabric' => 'Microfibra Spandex / Licra',
            'fabric_id' => $fabricLicra->id,
            'section_name' => 'basicos',
            'type' => 'dropship',
            'price' => 195.00,
            'cost' => 85.00,
            'visible' => false,
            'is_archived' => true,
            'archived_at' => now()->subDays(2),
            'discontinued_reason' => 'Producto retirado por proveedor Torzo en último barrido de scraping',
            'slug' => 'bikini-microfibra-torzo-retirado-en-origen',
            'meta' => [
                'category' => 'Bikini',
                'color_name' => 'Negro Profundo',
                'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=400&auto=format&fit=crop&q=60',
            ],
        ]);

        $varDisc = ProductVariant::create([
            'product_id' => $discontinuedProduct->id,
            'sku' => 'TOR-DISC-DEMO-M',
            'price' => 195.00,
            'cost' => 85.00,
            'attributes' => ['size' => 'M'],
        ]);

        Inventory::create([
            'variant_id' => $varDisc->id,
            'quantity' => 0,
            'reserved' => 0,
            'location' => 'Almacén Proveedor Remoto',
            'min_threshold' => 0,
        ]);
    }
}
