<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(10)->create()->each(function (Product $product) {
            // create 6 variants per product (3 sizes x 2 colors)
            $sizes = ['S','M','L'];
            $colors = ['negro','blanco'];

            foreach ($sizes as $size) {
                foreach ($colors as $color) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => strtoupper('V'.bin2hex(random_bytes(5))),
                        'attributes' => ['size' => $size, 'color' => $color],
                        'price' => $product->price,
                        'cost' => $product->cost,
                        'visible' => true,
                    ]);

                    Inventory::create([
                        'variant_id' => $variant->id,
                        'location' => 'warehouse-1',
                        'quantity' => rand(5, 50),
                        'reserved' => 0,
                        'type' => 'physical',
                    ]);
                }
            }
        });
    }
}
