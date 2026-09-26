<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\SupplierScrapers\TorzoScraper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TorzoProviderSeeder extends Seeder
{
    public function run(): void
    {
        $scraper = new TorzoScraper();

        $supplier = Supplier::updateOrCreate(
            ['website' => 'https://torzo.kexe.mx/'],
            [
                'name' => 'Torzo',
                'website' => 'https://torzo.kexe.mx/',
                'type' => 'dropship_scrape',
                'credentials' => null,
                'notes' => 'Proveedor Torzo importado automáticamente',
            ]
        );

        $this->command?->info("Proveedor creado/actualizado: {$supplier->name} (id={$supplier->id})");

        try {
            $items = $scraper->fetchCatalog();
        } catch (\Throwable $e) {
            $this->command?->warn("No se pudo conectar en vivo a Torzo: " . $e->getMessage() . ". Usando SupplierProducts existentes.");
            $items = [];
        }

        $count = 0;

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = (object) $item;
            }

            $data = $scraper->normalizeProduct($item);

            if (empty($data['supplier_sku'])) {
                continue;
            }

            $sp = SupplierProduct::updateOrCreate(
                [
                    'supplier_id' => $supplier->id,
                    'supplier_sku' => $data['supplier_sku'],
                ],
                array_merge($data, ['supplier_id' => $supplier->id])
            );

            $snapshot = is_array($sp->raw_snapshot) ? $sp->raw_snapshot : [];
            $title = $snapshot['title'] ?? $snapshot['name'] ?? $snapshot['nombreProducto'] ?? $sp->supplier_sku;
            $title = trim(Str::title(str_replace(['_', '-'], ' ', (string) $title)));
            $slug = Str::slug($title . '-' . $sp->supplier_sku);
            $price = (float) ($sp->price && $sp->price > 0 ? $sp->price : 199.00);
            $cost = round($price * 0.5, 2);
            $brand = $snapshot['brand'] ?? 'Torzo';
            $category = $snapshot['categoria'] ?? $snapshot['category'] ?? 'Ropa Interior';
            $images = $snapshot['images'] ?? array_values(array_filter([
                $snapshot['imagen1'] ?? null,
                $snapshot['imagen2'] ?? null,
            ]));
            $mainImage = $images[0] ?? $snapshot['imagen1'] ?? null;
            if ($mainImage && !Str::startsWith($mainImage, ['http://', 'https://'])) {
                $mainImage = 'https://torzo.kexe.mx/' . ltrim($mainImage, '/.');
            }

            $product = Product::updateOrCreate(
                ['sku' => $sp->supplier_sku],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'description' => $snapshot['description'] ?? "Prenda de diseño {$title} importada del catálogo oficial de {$brand}.",
                    'brand' => $brand,
                    'visible' => true,
                    'type' => 'dropship',
                    'price' => $price,
                    'cost' => $cost,
                    'meta' => [
                        'images' => $images,
                        'image' => $mainImage,
                        'category' => $category,
                        'garment_type' => $category,
                        'supplier_id' => $supplier->id,
                    ],
                ]
            );

            $sp->product_id = $product->id;
            $sp->save();

            $variations = $snapshot['variations'] ?? [];
            if (empty($variations)) {
                $variations = [['size' => 'U', 'stock' => 15, 'color' => 'Unico']];
            }

            foreach ($variations as $idx => $var) {
                $size = is_array($var) ? ($var['size'] ?? $var['title'] ?? 'U') : 'U';
                $color = is_array($var) ? ($var['color'] ?? 'Unico') : 'Unico';
                $varStock = is_array($var) ? (isset($var['stock']) && $var['stock'] > 0 ? (int) $var['stock'] : 15) : 15;
                $cleanSize = preg_replace('/[^a-zA-Z0-9]/', '', (string)$size);
                $varSku = $sp->supplier_sku . '-' . strtoupper($cleanSize ?: ('V' . ($idx + 1)));

                $variant = ProductVariant::updateOrCreate(
                    ['sku' => $varSku],
                    [
                        'product_id' => $product->id,
                        'attributes' => ['size' => (string)$size, 'color' => (string)$color],
                        'price' => $product->price,
                        'cost' => $product->cost,
                        'visible' => true,
                    ]
                );

                Inventory::updateOrCreate(
                    ['variant_id' => $variant->id, 'location' => 'warehouse-1'],
                    [
                        'quantity' => $varStock,
                        'reserved' => 0,
                        'type' => 'dropship',
                    ]
                );
            }

            $count++;
        }

        $this->command?->info("Imported or updated {$count} products for Torzo.");
    }
}

