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
        $metaCategory = (string) data_get($product->meta, 'category', '');
        $metaSubcategory = (string) data_get($product->meta, 'subcategory', '');
        $categoryText = strtoupper(trim(
            $product->categories->pluck('name')->merge($product->categories->pluck('slug'))->implode(' ')
            . ' ' . $metaCategory
            . ' ' . $metaSubcategory
            . ' ' . $product->title
        ));

        $categorySlug = 'jocks';
        $categoryName = 'Jockstraps';

        if (str_contains($categoryText, 'BOXER') || str_contains($categoryText, 'BRIEF')) {
            $categorySlug = 'boxers';
            $categoryName = 'Boxers';
        } elseif (str_contains($categoryText, 'TANGA')) {
            $categorySlug = 'tangas';
            $categoryName = 'Tangas';
        } elseif (str_contains($categoryText, 'BIKINI')) {
            $categorySlug = 'bikinis';
            $categoryName = 'Bikinis';
        } elseif (str_contains($categoryText, 'TANK') || str_contains($categoryText, 'SHIRT') || str_contains($categoryText, 'TOP')) {
            $categorySlug = 'tanktops';
            $categoryName = 'Tank Tops';
        } elseif (str_contains($categoryText, 'SWIM') || str_contains($categoryText, 'BAÑO')) {
            $categorySlug = 'swimwear';
            $categoryName = 'Trajes de Baño';
        } elseif (str_contains($categoryText, 'JOCK')) {
            $categorySlug = 'jocks';
            $categoryName = 'Jockstraps';
        } elseif ($product->categories->isNotEmpty()) {
            $categorySlug = $product->categories->first()->slug;
            $categoryName = $product->categories->first()->name;
        } elseif (!empty($metaCategory)) {
            $categorySlug = \Illuminate\Support\Str::slug($metaCategory);
            $categoryName = $metaCategory;
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
            $metaSizes = data_get($product->meta, 'sizes', []);
            if (is_array($metaSizes) && !empty($metaSizes)) {
                $sizes = array_map(fn($s) => strtoupper(trim((string) $s)), array_keys($metaSizes));
            }
        }
        if (empty($sizes)) {
            $curve = (new \App\Services\Catalog\ProductCatalogSyncService())->resolveSizeCurveForProduct($product);
            if ($curve && $curve->sizes->isNotEmpty()) {
                $sizes = $curve->sizes->pluck('code')->map(fn($s) => strtoupper(trim((string) $s)))->all();
            }
        }
        if (empty($sizes)) {
            $sizes = ['CH', 'M', 'G', 'XL', '2XL', '3XL'];
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
            'category_name' => $categoryName,
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

