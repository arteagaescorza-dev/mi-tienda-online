<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\SupplierVariantMapping;
use App\Models\SupplierColorMapping;
use Illuminate\Support\Facades\DB;

class SupplierCatalogSyncService
{
    public function sync(Supplier $supplier, array $products): int
    {
        return DB::transaction(function () use ($supplier, $products): int {
            $synced = 0;

            foreach ($products as $product) {
                $supplierProduct = SupplierProduct::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'supplier_sku' => $product['supplier_sku'],
                    ],
                    [
                        'product_id' => $product['product_id'] ?? null,
                        'supplier_url' => $product['supplier_url'] ?? null,
                        'last_checked_at' => now(),
                        'price' => $product['price'] ?? null,
                        'availability' => $product['availability'] ?? 'unknown',
                        'raw_snapshot' => $product['raw_snapshot'] ?? [],
                    ]
                );

                $this->syncDropshipInventory($supplierProduct, $product);
                $synced++;
            }

            return $synced;
        });
    }

    private function syncDropshipInventory(SupplierProduct $supplierProduct, array $product): void
    {
        if (! $supplierProduct->product_id) {
            return;
        }

        $stock = data_get($product, 'raw_snapshot.stock');
        if (! is_numeric($stock)) {
            return;
        }

        $variants = Product::query()->find($supplierProduct->product_id)?->variants()->get() ?? collect();
        if ($variants->isEmpty()) {
            return;
        }

        $variations = data_get($product, 'raw_snapshot.variations', []);
        if (is_array($variations) && count($variations) > 0) {
            foreach ($variations as $variation) {
                if (! is_numeric($variation['stock'] ?? null)) {
                    continue;
                }

                $matchingVariants = $variants->filter(function ($variant) use ($variation, $supplierProduct): bool {
                    $attributes = $variant->attributes ?? [];
                    $sizeMatches = ! isset($variation['size']) || data_get($attributes, 'size') === $variation['size'];
                    $externalColor = $variation['color'] ?? null;
                    $localColor = $externalColor === null ? null : SupplierColorMapping::query()
                        ->where('supplier_id', $supplierProduct->supplier_id)
                        ->whereRaw('LOWER(external_color) = ?', [strtolower((string) $externalColor)])
                        ->value('local_color');
                    $localColor = $localColor ?? $externalColor;
                    $colorMatches = ! isset($localColor) || data_get($attributes, 'color') === $localColor;

                    return $sizeMatches && $colorMatches;
                });
                $hasColor = isset($variation['color']) && $variation['color'] !== null && $variation['color'] !== '';
                $matchedVariant = $matchingVariants->count() === 1 || $hasColor ? $matchingVariants->first() : null;

                if ($matchedVariant) {
                    $variantKey = (string) ($variation['sku'] ?? $variation['id'] ?? $variation['size'] . '-' . ($variation['color'] ?? 'default'));
                    SupplierVariantMapping::updateOrCreate(
                        ['supplier_product_id' => $supplierProduct->id, 'supplier_variant_key' => $variantKey],
                        [
                            'product_variant_id' => $matchedVariant->id,
                            'attributes' => $variation,
                            'stock' => (int) $variation['stock'],
                            'available' => (bool) ($variation['available'] ?? ((int) $variation['stock'] > 0)),
                        ]
                    );
                    $this->upsertDropshipInventory($supplierProduct, $matchedVariant->id, (int) $variation['stock']);
                }
            }

            return;
        }

        $this->upsertDropshipInventory($supplierProduct, $variants->first()->id, (int) $stock);
    }

    private function upsertDropshipInventory(SupplierProduct $supplierProduct, int $variantId, int $stock): void
    {
        Inventory::updateOrCreate(
            [
                'variant_id' => $variantId,
                'location' => 'supplier-' . $supplierProduct->supplier_id,
                'type' => 'dropship',
            ],
            [
                'quantity' => max(0, $stock),
                'reserved' => 0,
            ]
        );
    }
}