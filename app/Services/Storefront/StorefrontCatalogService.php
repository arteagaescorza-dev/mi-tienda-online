<?php

namespace App\Services\Storefront;

use App\Models\Product;

class StorefrontCatalogService
{
    /**
     * Genera el payload enriquecido de un producto para la vista de detalle (PDP),
     * resolviendo métricas de inventario (físico vs bajo pedido) y categorización semántica.
     */
    public function getProductDetailPayload(string $slug): ?array
    {
        $product = Product::query()
            ->with([
                'variants.inventories',
                'categories',
            ])
            ->where('visible', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('id', $slug)
                    ->orWhere('sku', $slug);
            })
            ->first();

        if (! $product) {
            return null;
        }

        $variants = $product->variants;
        $totalPhysical = (int) $variants->sum(fn ($v) => $v->inventories->sum('quantity'));
        $totalReserved = (int) $variants->sum(fn ($v) => $v->inventories->sum('reserved'));
        $totalAvailable = max(0, $totalPhysical - $totalReserved);

        $sizeSubMap = [
            'CH' => ['pants' => '28-30', 'waist_cm' => '70-76 cm', 'label' => 'Chica'],
            'M' => ['pants' => '31-32', 'waist_cm' => '78-84 cm', 'label' => 'Mediana'],
            'G' => ['pants' => '33-34', 'waist_cm' => '86-92 cm', 'label' => 'Grande'],
            'XG' => ['pants' => '35-36', 'waist_cm' => '94-102 cm', 'label' => 'Extra Grande'],
            'XL' => ['pants' => '35-36', 'waist_cm' => '94-102 cm', 'label' => 'Extra Grande'],
            '2XL' => ['pants' => '38-40', 'waist_cm' => '104-110 cm', 'label' => 'Doble Extra Grande'],
            '3XL' => ['pants' => '42-44', 'waist_cm' => '112-120 cm', 'label' => 'Triple Extra Grande'],
        ];

        $variantList = $variants->map(function ($v) use ($sizeSubMap) {
            $qty = (int) $v->inventories->sum('quantity');
            $res = (int) $v->inventories->sum('reserved');
            $avail = max(0, $qty - $res);
            $size = strtoupper(trim((string) data_get($v->attributes, 'size', 'M')));

            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'size' => $size,
                'color' => data_get($v->attributes, 'color', 'Unico'),
                'price' => (float) $v->price,
                'price_formatted' => 'MXN $' . number_format((float) $v->price, 2),
                'stock_physical' => $qty,
                'stock_reserved' => $res,
                'stock_available' => $avail,
                'is_available' => $avail > 0,
                'stock_badge' => $avail > 0 ? 'Stock Físico' : 'Bajo Pedido',
                'sub_info' => $sizeSubMap[$size] ?? ['pants' => '-', 'waist_cm' => '-', 'label' => 'Talla ' . $size],
            ];
        })->values();

        // Categoría slug y universos
        $brandUpper = strtoupper((string) ($product->brand ?: data_get($product->meta, 'category', '')));
        $categorySlug = 'jocks';
        if (str_contains($brandUpper, 'BOXER') || str_contains($brandUpper, 'BRIEF')) {
            $categorySlug = 'boxers';
        } elseif (str_contains($brandUpper, 'TANGA')) {
            $categorySlug = 'tangas';
        } elseif (str_contains($brandUpper, 'BIKINI')) {
            $categorySlug = 'bikinis';
        } elseif (str_contains($brandUpper, 'TANK') || str_contains($brandUpper, 'SHIRT')) {
            $categorySlug = 'tanktops';
        } elseif (str_contains($brandUpper, 'SWIM')) {
            $categorySlug = 'swimwear';
        }

        $universes = ['marte', 'terra'];
        if (in_array($categorySlug, ['bikinis', 'swimwear'])) {
            $universes = ['marte', 'venus', 'terra'];
        } elseif ($categorySlug === 'tanktops') {
            $universes = ['marte', 'tiresias', 'terra'];
        }
        if ($product->meta && isset($product->meta['universes']) && is_array($product->meta['universes'])) {
            $universes = $product->meta['universes'];
        }

        $sizes = $variantList->pluck('size')->unique()->values()->all();
        if (empty($sizes)) {
            $sizes = ['CH', 'M', 'G', 'XG'];
        }

        $image = data_get($product->meta, 'image') ?: (data_get($product->meta, 'images.0') ?: '/user-avatar.png');
        $image = str_replace('/./', '/', $image);
        $images = data_get($product->meta, 'images', [$image]);

        $fabricComposition = data_get($product->meta, 'fabric_composition')
            ?: '95% Modal Pima peruano / 5% Elastano (Spandex) de alta recuperación. Elástico jacquard de 45mm.';

        return [
            'id' => (string) $product->id,
            'sku' => $product->sku,
            'name' => $product->title,
            'slug' => $product->slug,
            'brand' => $product->brand ?: 'Torzo',
            'category' => $categorySlug,
            'category_name' => $product->brand ?: (data_get($product->meta, 'category', 'Prenda')),
            'price' => (float) $product->price,
            'price_formatted' => 'MXN $' . number_format((float) $product->price, 2),
            'fabric_composition' => $fabricComposition,
            'stock_type' => $totalAvailable > 0 ? 'ready' : 'on_demand',
            'stock_label' => $totalAvailable > 0 ? 'Stock Físico' : 'Bajo Pedido',
            'total_physical_stock' => $totalPhysical,
            'total_reserved_stock' => $totalReserved,
            'total_available_stock' => $totalAvailable,
            'image' => $image,
            'images' => $images,
            'description' => $product->description ?: 'Prenda anatómica confeccionada con textiles transpirables y soporte ergonómico.',
            'universes' => $universes,
            'sizes' => $sizes,
            'variants' => $variantList,
        ];
    }
}
