<?php

namespace App\Services\Catalog;

use App\Models\ApparelColor;
use App\Models\ApparelSizeCurve;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductCatalogSyncService
{
    /**
     * Sincroniza categorías y subcategorías de un producto en la base de datos (relación y meta).
     */
    public function syncProductCategories(Product $product, ?string $categoryName = null, ?string $subcategoryName = null): void
    {
        $meta = (array) ($product->meta ?? []);
        $catName = trim((string) ($categoryName ?? data_get($meta, 'category', '')));
        $subCatName = trim((string) ($subcategoryName ?? data_get($meta, 'subcategory', '')));

        if (empty($catName) && $product->categories()->exists()) {
            $existing = $product->categories()->whereNull('parent_id')->first() ?: $product->categories()->first();
            if ($existing) {
                $catName = $existing->name;
            }
        }

        if (empty($catName)) {
            $catName = 'General';
        }

        // Buscar categoría existente insensible a mayúsculas o plurales comunes (ej: Bikini / Bikinis, Tanga / Tangas)
        $parentCat = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($catName)])
            ->orWhereRaw('LOWER(slug) = ?', [Str::slug($catName)])
            ->first();

        if (!$parentCat) {
            $singular = rtrim($catName, 'sS');
            $plural = $singular . 's';
            $parentCat = Category::whereRaw('LOWER(name) IN (?, ?)', [mb_strtolower($singular), mb_strtolower($plural)])->first();
        }

        if (!$parentCat) {
            $parentCat = Category::create([
                'name' => $catName,
                'slug' => Str::slug($catName),
                'is_active' => true,
            ]);
        }

        $categoryIds = [$parentCat->id];

        if (!empty($subCatName)) {
            $subCat = Category::where('parent_id', $parentCat->id)
                ->where(function ($q) use ($subCatName) {
                    $q->whereRaw('LOWER(name) = ?', [mb_strtolower($subCatName)])
                      ->orWhereRaw('LOWER(slug) = ?', [Str::slug($subCatName)]);
                })
                ->first();

            if (!$subCat) {
                $subCat = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($subCatName)])->first();
            }

            if (!$subCat) {
                $subCat = Category::create([
                    'name' => $subCatName,
                    'slug' => Str::slug($subCatName),
                    'parent_id' => $parentCat->id,
                    'is_active' => true,
                ]);
            } elseif (empty($subCat->parent_id)) {
                $subCat->parent_id = $parentCat->id;
                $subCat->save();
            }

            $categoryIds[] = $subCat->id;
        }

        $product->categories()->sync($categoryIds);

        $meta['category'] = $parentCat->name;
        $meta['subcategory'] = !empty($subCatName) ? ($subCat?->name ?? $subCatName) : '';
        $product->meta = $meta;
        $product->saveQuietly();
    }

    /**
     * Sincroniza colores del producto con el catálogo maestro de ApparelColor.
     */
    public function syncProductColors(Product $product, ?string $colorName = null, ?string $colorHex = null, ?string $colorType = null): void
    {
        $meta = (array) ($product->meta ?? []);
        $mainColor = trim((string) ($colorName ?? data_get($meta, 'color_name', '')));
        $hex = $colorHex ?? data_get($meta, 'color_hex');
        $type = $colorType ?? data_get($meta, 'color_type', 'solid');

        // Extraer todos los colores de la galería o mapa
        $colorsToEnsure = [];
        if (!empty($mainColor)) {
            $colorsToEnsure[$mainColor] = [
                'hex' => $hex,
                'type' => $type,
            ];
        }

        $gallery = data_get($meta, 'gallery', []);
        if (is_array($gallery)) {
            foreach ($gallery as $g) {
                $c = is_array($g) ? ($g['color_name'] ?? null) : null;
                if (!empty($c) && !isset($colorsToEnsure[$c])) {
                    $colorsToEnsure[$c] = ['hex' => null, 'type' => 'solid'];
                }
            }
        }

        $imagesByColor = data_get($meta, 'images_by_color', []) ?: data_get($meta, 'image_color_map', []);
        if (is_array($imagesByColor)) {
            foreach ($imagesByColor as $url => $c) {
                if (is_string($c) && !empty($c) && !isset($colorsToEnsure[$c])) {
                    $colorsToEnsure[$c] = ['hex' => null, 'type' => 'solid'];
                }
            }
        }

        foreach ($colorsToEnsure as $cName => $info) {
            $lower = mb_strtolower(trim((string) $cName));
            $appColor = ApparelColor::whereRaw('LOWER(name) = ?', [$lower])->first();

            $isTricolor = str_contains($lower, 'tricolor');
            $isMulticolor = str_contains($lower, 'multi');

            $cType = $info['type'] ?? 'solid';
            $cHex = $info['hex'];
            $cGradient = null;

            if ($isTricolor) {
                $cType = 'gradient';
                $cHex = '#15803d';
                $cGradient = 'linear-gradient(135deg, #15803d 0%, #15803d 33%, #ffffff 33%, #ffffff 66%, #b91c1c 66%, #b91c1c 100%)';
            } elseif ($isMulticolor) {
                $cType = 'gradient';
                $cHex = '#ef4444';
                $cGradient = 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)';
            } elseif (!empty($cHex) && str_contains($cHex, 'gradient')) {
                $cType = 'gradient';
                $cGradient = $cHex;
                $cHex = '#475569';
            } elseif (empty($cHex) || $cHex === '#0f172a') {
                $cHex = \App\Http\Controllers\StorefrontController::detectColorHex($cName);
                if (str_contains($cHex, 'gradient')) {
                    $cType = 'gradient';
                    $cGradient = $cHex;
                    $cHex = '#475569';
                }
            }

            if (!$appColor) {
                ApparelColor::create([
                    'name' => $cName,
                    'hex_code' => $cHex ?: '#0f172a',
                    'color_type' => $cType,
                    'gradient_css' => $cGradient,
                    'is_active' => true,
                ]);
            } else {
                if ($isTricolor && (empty($appColor->gradient_css) || $appColor->color_type !== 'gradient')) {
                    $appColor->update([
                        'color_type' => 'gradient',
                        'gradient_css' => $cGradient,
                        'hex_code' => $cHex,
                    ]);
                }
            }
        }

        // Actualizar datos de color en el producto
        if (!empty($mainColor)) {
            $resolvedColor = ApparelColor::whereRaw('LOWER(name) = ?', [mb_strtolower($mainColor)])->first();
            $meta['color_name'] = $mainColor;
            if ($resolvedColor) {
                $meta['color_hex'] = $resolvedColor->color_type === 'gradient' ? ($resolvedColor->gradient_css ?: $resolvedColor->hex_code) : $resolvedColor->hex_code;
                $meta['color_type'] = $resolvedColor->color_type;
            } elseif ($hex) {
                $meta['color_hex'] = $hex;
                $meta['color_type'] = $type;
            }
            $product->meta = $meta;
            $product->saveQuietly();
        }
    }

    /**
     * Sincroniza curvas de tallas y variantes de un producto.
     */
    public function syncProductSizesAndCurve(Product $product, ?array $sizes = null): void
    {
        $meta = (array) ($product->meta ?? []);
        $existingSizes = $sizes ?? data_get($meta, 'sizes');

        // Si no tiene tallas definidas o es un array vacío, resolver por curva de talla asociada
        if (empty($existingSizes) || !is_array($existingSizes)) {
            $curve = $this->resolveSizeCurveForProduct($product);
            if ($curve && $curve->sizes->isNotEmpty()) {
                $curveSizes = [];
                foreach ($curve->sizes as $s) {
                    $curveSizes[strtolower(trim($s->code))] = 0;
                }
                $existingSizes = $curveSizes;
            } else {
                $existingSizes = [
                    'ch' => 0,
                    'm' => 0,
                    'g' => 0,
                    'xl' => 0,
                    '2xl' => 0,
                    '3xl' => 0,
                ];
            }
        }

        $meta['sizes'] = $existingSizes;
        $product->meta = $meta;
        $product->saveQuietly();

        // Asegurar que existan ProductVariants para cada talla de la curva
        $product->load('variants.inventories');
        foreach ($existingSizes as $szKey => $targetQty) {
            $cleanSz = strtolower(trim((string) $szKey));
            $targetQty = max(0, (int) $targetQty);

            $variant = $product->variants->first(function ($v) use ($cleanSz) {
                return strtolower(trim((string) data_get($v->attributes, 'size', ''))) === $cleanSz;
            });

            if (!$variant) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $product->sku . '-' . strtoupper($cleanSz),
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'attributes' => ['size' => strtoupper($cleanSz)],
                ]);
            }

            $inv = Inventory::query()->lockForUpdate()->firstOrCreate(
                ['variant_id' => $variant->id, 'location' => 'warehouse-1'],
                ['quantity' => $targetQty, 'reserved' => 0, 'type' => 'physical']
            );

            // Si es un producto propio y la cantidad no coincide, actualizar inventario
            if ($product->type === 'physical' && $inv->quantity != $targetQty) {
                $inv->quantity = $targetQty;
                $inv->save();
            }
        }
    }

    /**
     * Resuelve la curva de tallas más idónea para un producto.
     */
    public function resolveSizeCurveForProduct(Product $product): ?ApparelSizeCurve
    {
        $meta = (array) ($product->meta ?? []);
        $brandName = trim((string) ($product->brand ?? data_get($meta, 'brand', '')));
        $supplierId = data_get($meta, 'supplier_id');

        // 1. Por marca exacta en ApparelSizeCurve
        if (!empty($brandName)) {
            $byBrand = ApparelSizeCurve::whereHas('brand', function ($q) use ($brandName) {
                $q->whereRaw('LOWER(name) = ?', [mb_strtolower($brandName)]);
            })->orWhereRaw('LOWER(name) = ?', [mb_strtolower($brandName)])->first();
            if ($byBrand) {
                return $byBrand;
            }
        }

        // 2. Por proveedor
        if (!empty($supplierId)) {
            $cleanSupId = (int) str_replace('prov_', '', (string) $supplierId);
            $bySupplier = ApparelSizeCurve::where('supplier_id', $cleanSupId)->first();
            if ($bySupplier) {
                return $bySupplier;
            }
        }

        // 3. Curva por defecto
        return ApparelSizeCurve::where('is_default', true)->first();
    }

    /**
     * Sincronización integral de un producto.
     */
    public function syncProduct(Product $product, array $attributes = []): Product
    {
        $this->syncProductCategories(
            $product,
            $attributes['category'] ?? null,
            $attributes['subcategory'] ?? null
        );

        $this->syncProductColors(
            $product,
            $attributes['color_name'] ?? null,
            $attributes['color_hex'] ?? null,
            $attributes['color_type'] ?? null
        );

        $this->syncProductSizesAndCurve(
            $product,
            $attributes['sizes'] ?? null
        );

        return $product->fresh(['categories', 'variants.inventories']);
    }

    /**
     * Sincronización masiva de todo el catálogo.
     */
    public function syncAllProducts(): int
    {
        $products = Product::all();
        $count = 0;
        foreach ($products as $product) {
            $this->syncProduct($product);
            $count++;
        }
        return $count;
    }

    /**
     * Propaga la actualización de una categoría a todos los productos vinculados.
     */
    public function propagateCategoryUpdate(Category $category, string $oldName): void
    {
        $products = Product::whereHas('categories', function ($q) use ($category) {
            $q->where('categories.id', $category->id);
        })->orWhere('meta->category', $oldName)->orWhere('meta->subcategory', $oldName)->get();

        foreach ($products as $p) {
            $meta = (array) ($p->meta ?? []);
            if (empty($category->parent_id)) {
                $meta['category'] = $category->name;
            } else {
                $meta['subcategory'] = $category->name;
                $parent = Category::find($category->parent_id);
                if ($parent) {
                    $meta['category'] = $parent->name;
                }
            }
            $p->meta = $meta;
            $p->saveQuietly();
            $this->syncProductCategories($p, $meta['category'] ?? null, $meta['subcategory'] ?? null);
        }
    }

    /**
     * Propaga la actualización de un color a todos los productos que lo usan.
     */
    public function propagateColorUpdate(ApparelColor $color, string $oldName): void
    {
        $products = Product::where('meta->color_name', $oldName)
            ->orWhere('meta->color_name', $color->name)
            ->get();

        foreach ($products as $p) {
            $meta = (array) ($p->meta ?? []);
            $meta['color_name'] = $color->name;
            $meta['color_hex'] = $color->color_type === 'gradient' ? ($color->gradient_css ?: $color->hex_code) : $color->hex_code;
            $meta['color_type'] = $color->color_type;
            $p->meta = $meta;
            $p->saveQuietly();
        }
    }

    /**
     * Propaga la actualización de una curva de tallas a los productos asociados.
     */
    public function propagateCurveUpdate(ApparelSizeCurve $curve): void
    {
        $curve->load('sizes');
        $sizeCodes = $curve->sizes->pluck('code')->map(fn($c) => strtolower(trim($c)))->toArray();

        $query = Product::query();
        if ($curve->brand_id) {
            $query->where('brand', $curve->brand?->name);
        } elseif ($curve->supplier_id) {
            $query->where(function ($q) use ($curve) {
                $q->where('meta->supplier_id', 'prov_' . $curve->supplier_id)
                  ->orWhere('meta->supplier_id', (string) $curve->supplier_id);
            });
        }

        $products = $query->get();
        foreach ($products as $p) {
            $meta = (array) ($p->meta ?? []);
            $currentSizes = (array) data_get($meta, 'sizes', []);
            $newSizes = [];
            foreach ($sizeCodes as $code) {
                $newSizes[$code] = $currentSizes[$code] ?? 0;
            }
            $this->syncProductSizesAndCurve($p, $newSizes);
        }
    }
}

