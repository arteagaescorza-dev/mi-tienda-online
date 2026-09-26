<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\SupplierScrapers\AdrenalineForteScraper;
use Illuminate\Database\Seeder;

class AdrenalineForteProviderSeeder extends Seeder
{
    public function run(): void
    {
        $scraper = new AdrenalineForteScraper();

        $supplier = Supplier::updateOrCreate(
            ['website' => 'https://www.adrenalineunderwear.com/collections/all'],
            [
                'name' => 'Adrenaline Forte',
                'website' => 'https://www.adrenalineunderwear.com/collections/all',
                'type' => 'dropship_scrape',
                'credentials' => null,
                'notes' => 'Proveedor Adrenaline Forte importado automáticamente',
            ]
        );

        $this->command?->info("Proveedor creado/actualizado: {$supplier->name} (id={$supplier->id})");

        $items = $scraper->fetchCatalog();
        $count = 0;

        foreach ($items as $item) {
            $data = $scraper->normalizeProduct($item);

            if (empty($data['supplier_sku'])) {
                continue;
            }

            SupplierProduct::updateOrCreate(
                [
                    'supplier_id' => $supplier->id,
                    'supplier_sku' => $data['supplier_sku'],
                ],
                array_merge($data, ['supplier_id' => $supplier->id])
            );

            $count++;
        }

        $this->command?->info("Imported or updated {$count} supplier_products for Adrenaline Forte.");
    }
}
