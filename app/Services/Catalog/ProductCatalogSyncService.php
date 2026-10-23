<?php

namespace App\Services\Catalog;

use App\Models\ApparelColor;
use App\Models\ApparelSizeCurve;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
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
        $rawSizes = $sizes ?? data_get($meta, 'sizes');
        $existingSizes = [];

        if (is_array($rawSizes) && !empty($rawSizes)) {
            foreach ($rawSizes as $k => $v) {
                if (is_array($v)) {
                    $sName = $v['name'] ?? ($v['size'] ?? ($v['code'] ?? ($v['talla'] ?? (string) $k)));
                    $sQty = $v['stock'] ?? ($v['quantity'] ?? ($v['qty'] ?? 0));
                    $existingSizes[strtolower(trim((string) $sName))] = (int) $sQty;
                } else {
                    $existingSizes[strtolower(trim((string) $k))] = (int) $v;
                }
            }
        }

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

        $isScrapedProduct = $product->isScraped() || $product->type === 'dropship';
        if ($isScrapedProduct) {
            foreach ($existingSizes as $k => $v) {
                $existingSizes[$k] = 0;
            }
        }

        $meta['sizes'] = $existingSizes;
        if ($isScrapedProduct) {
            $meta['is_scraped'] = true;
            $meta['local_stock'] = 0;
            $meta['physical_stock'] = 0;
            $meta['available_stock'] = 0;
        }
        $product->meta = $meta;
        $product->saveQuietly();

        // Asegurar que existan ProductVariants para cada talla de la curva
        $product->load('variants.inventories');
        foreach ($existingSizes as $szKey => $targetQty) {
            $cleanSz = strtolower(trim((string) $szKey));
            $targetQty = $isScrapedProduct ? 0 : max(0, (int) $targetQty);

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
            } else {
                if ($variant->price != $product->price || $variant->cost != $product->cost) {
                    $variant->update([
                        'price' => $product->price,
                        'cost' => $product->cost,
                    ]);
                }
            }

            $inv = Inventory::query()->lockForUpdate()->where('variant_id', $variant->id)->where('location', 'warehouse-1')->first();

            if (! $inv) {
                $inv = Inventory::create([
                    'variant_id' => $variant->id,
                    'location' => 'warehouse-1',
                    'quantity' => $targetQty,
                    'reserved' => 0,
                    'type' => $isScrapedProduct ? 'dropship' : 'physical',
                ]);

                if ($targetQty > 0) {
                    InventoryMovement::create([
                        'inventory_id' => $inv->id,
                        'user_id' => auth()->id(),
                        'quantity' => $targetQty,
                        'quantity_before' => 0,
                        'quantity_after' => $targetQty,
                        'reason' => "Alta inicial de inventario para SKU {$variant->sku}",
                    ]);
                }
            } else {
                if ($isScrapedProduct) {
                    if ($inv->quantity !== 0 || $inv->reserved !== 0 || $inv->type !== 'dropship') {
                        $inv->quantity = 0;
                        $inv->reserved = 0;
                        $inv->type = 'dropship';
                        $inv->save();
                    }
                } else {
                    $currentQty = (int) $inv->quantity;
                    $diff = $targetQty - $currentQty;

                    if ($product->type === 'physical' && $diff !== 0) {
                        $inv->quantity = $targetQty;
                        $inv->save();

                        InventoryMovement::create([
                            'inventory_id' => $inv->id,
                            'user_id' => auth()->id(),
                            'quantity' => $diff,
                            'quantity_before' => $currentQty,
                            'quantity_after' => $targetQty,
                            'reason' => "Ajuste de talla '{$cleanSz}' desde edicion integral de SKU ({$product->sku})",
                        ]);
                    }
                }
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
     * Sincronización de precios, costos, márgenes e inventario.
     */
    public function syncProductPricesAndStock(Product $product, array $attributes = []): void
    {
        $meta = (array) ($product->meta ?? []);
        $updated = false;

        $pvp = isset($attributes['pvp']) || isset($attributes['price'])
            ? (float) ($attributes['pvp'] ?? $attributes['price'])
            : (float) $product->price;

        $cost = isset($attributes['cost'])
            ? (float) $attributes['cost']
            : (float) $product->cost;

        if ($product->price != $pvp || $product->cost != $cost) {
            $product->price = $pvp;
            $product->cost = $cost;
            $updated = true;
        }

        $regularPrice = isset($attributes['regular_price']) ? (float) $attributes['regular_price'] : (float) data_get($meta, 'regular_price', $pvp);
        $discountPct = isset($attributes['discount_pct']) ? (float) $attributes['discount_pct'] : (float) data_get($meta, 'discount_pct', 0);
        $effectivePrice = $discountPct > 0 ? round($pvp * (1 - ($discountPct / 100)), 2) : $pvp;
        $cpp = isset($attributes['cpp']) || isset($attributes['weighted_average_cost'])
            ? (float) ($attributes['cpp'] ?? $attributes['weighted_average_cost'])
            : (float) data_get($meta, 'weighted_average_cost', $cost);

        $meta['regular_price'] = $regularPrice;
        $meta['discount_pct'] = $discountPct;
        $meta['effective_price'] = $effectivePrice;
        $meta['weighted_average_cost'] = $cpp;

        // Calcular existencias físicas y disponibles en tiempo real desde variantes
        $product->load('variants.inventories');
        $isScrapedProduct = $product->isScraped() || $product->type === 'dropship';
        $totalPhysical = $isScrapedProduct ? 0 : (int) $product->variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('quantity'));
        $totalReserved = $isScrapedProduct ? 0 : (int) $product->variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('reserved'));
        $totalAvailable = max(0, $totalPhysical - $totalReserved);

        $meta['local_stock'] = $totalPhysical;
        $meta['physical_stock'] = $totalPhysical;
        $meta['available_stock'] = $totalAvailable;
        if ($isScrapedProduct) {
            $meta['is_scraped'] = true;
            $meta['stock'] = 0;
        }

        $product->meta = $meta;
        if ($updated || $product->isDirty('meta')) {
            $product->saveQuietly();
        }

        // Sincronizar precio y costo en todas las variantes existentes
        foreach ($product->variants as $variant) {
            if ($variant->price != $pvp || $variant->cost != $cost) {
                $variant->update([
                    'price' => $pvp,
                    'cost' => $cost,
                ]);
            }
        }
    }

    /**
     * Sincronización de descripción, atributos textiles y metadatos comerciales.
     */
    public function syncProductContent(Product $product, array $attributes = []): void
    {
        $meta = (array) ($product->meta ?? []);
        $updated = false;

        if (array_key_exists('description', $attributes) && $product->description !== $attributes['description']) {
            $product->description = (string) $attributes['description'];
            $updated = true;
        }

        if (array_key_exists('title', $attributes) && !empty($attributes['title']) && $product->title !== $attributes['title']) {
            $product->title = (string) $attributes['title'];
            $updated = true;
        } elseif (array_key_exists('name', $attributes) && !empty($attributes['name']) && $product->title !== $attributes['name']) {
            $product->title = (string) $attributes['name'];
            $updated = true;
        }

        if (array_key_exists('brand', $attributes) && !empty($attributes['brand']) && $product->brand !== $attributes['brand']) {
            $product->brand = (string) $attributes['brand'];
            $meta['brand'] = $product->brand;
            $updated = true;
        }

        $fabricName = $attributes['fabric'] ?? ($attributes['fabric_type'] ?? null);
        if (!empty($fabricName)) {
            $product->fabric = $fabricName;
            $meta['fabric'] = $fabricName;
            $meta['fabric_type'] = $fabricName;
            if (!empty($attributes['fabric_id'])) {
                $product->fabric_id = (int) $attributes['fabric_id'];
                $meta['fabric_id'] = (int) $attributes['fabric_id'];
            }
            $updated = true;
        }

        if (array_key_exists('bullet_points', $attributes)) {
            $meta['bullet_points'] = (array) $attributes['bullet_points'];
        }
        if (array_key_exists('seo_tags', $attributes)) {
            $meta['seo_tags'] = (array) $attributes['seo_tags'];
        }

        $product->meta = $meta;
        if ($updated || $product->isDirty('meta')) {
            $product->saveQuietly();
        }
    }

    /**
     * Sincronización integral de un producto.
     */
    public function syncProduct(Product $product, array $attributes = []): Product
    {
        $this->syncProductContent($product, $attributes);

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

        $this->syncProductPricesAndStock($product, $attributes);

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

    /**
     * Propaga la actualización de una tela a todos los productos asociados.
     */
    public function propagateFabricUpdate(\App\Models\ApparelFabric $fabric, string $oldName): void
    {
        $products = Product::where('fabric_id', $fabric->id)
            ->orWhere('fabric', $oldName)
            ->orWhere('meta->fabric', $oldName)
            ->orWhere('meta->fabric_type', $oldName)
            ->get();

        foreach ($products as $p) {
            $meta = (array) ($p->meta ?? []);
            $meta['fabric'] = $fabric->name;
            $meta['fabric_type'] = $fabric->name;
            $meta['fabric_id'] = $fabric->id;
            if (!empty($fabric->description)) {
                $meta['fabric_composition'] = $fabric->description;
            }
            $p->fabric = $fabric->name;
            $p->fabric_id = $fabric->id;
            $p->meta = $meta;
            $p->saveQuietly();
        }
    }

    /**
     * Propaga la actualización de una marca comercial a todos los productos asociados.
     */
    public function propagateBrandUpdate(\App\Models\Brand $brand, string $oldName): void
    {
        $products = Product::where('brand', $oldName)
            ->orWhere('meta->brand', $oldName)
            ->get();

        foreach ($products as $p) {
            $meta = (array) ($p->meta ?? []);
            $meta['brand'] = $brand->name;
            $p->brand = $brand->name;
            $p->meta = $meta;
            $p->saveQuietly();
        }
    }
}

