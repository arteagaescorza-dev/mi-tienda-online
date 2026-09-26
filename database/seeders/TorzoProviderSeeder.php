<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\SupplierScrapers\TorzoScraper;
use Illuminate\Database\Seeder;

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

        $this->command->info("Proveedor creado/actualizado: {$supplier->name} (id={$supplier->id})");

        $items = $scraper->fetchCatalog();
        $count = 0;

        foreach ($items as $item) {
            // Ensure object
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

            // try to map to Product by sku
            $product = Product::where('sku', $sp->supplier_sku)->first();
            if ($product) {
                $sp->product_id = $product->id;
                $sp->save();

                // ensure a variant exists for this sku
                $variant = ProductVariant::firstWhere('sku', $sp->supplier_sku);
                if (! $variant) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sp->supplier_sku,
                        'attributes' => [],
                        'price' => $sp->price ?? $product->price,
                        'cost' => $product->cost ?? null,
                        'visible' => true,
                    ]);
                }
            }

            $count++;
        }

        $this->command->info("Imported or updated {$count} supplier_products for Torzo.");
    }
}
