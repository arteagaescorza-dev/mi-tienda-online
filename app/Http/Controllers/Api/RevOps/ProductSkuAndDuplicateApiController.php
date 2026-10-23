<?php

namespace App\Http\Controllers\Api\RevOps;

use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Services\Inventory\DuplicateDetectionService;
use App\Services\Inventory\ProductAiCopilotService;
use App\Services\Inventory\SkuGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RevOps\UpdateCompleteRequest;
use App\Http\Requests\Api\RevOps\StoreProductRequest;
use App\Http\Requests\Api\RevOps\GenerateDescriptionRequest;
use App\Http\Requests\Api\RevOps\CheckDuplicateRequest;
use App\Http\Requests\Api\RevOps\GenerateSkuRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSkuAndDuplicateApiController extends Controller
{
    public function __construct(
        protected SkuGeneratorService $skuGenerator,
        protected DuplicateDetectionService $duplicateDetector,
        protected ProductAiCopilotService $aiCopilot
    ) {}

    /**
     * Genera un codigo SKU estructurado y garantizado como unico.
     */
    public function generateSku(GenerateSkuRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $result = $this->skuGenerator->generateSku($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Codigo SKU generado correctamente.',
            'data' => $result,
        ]);
    }

    public function checkDuplicate(CheckDuplicateRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        if (empty($validated['title']) && !empty($validated['name'])) {
            $validated['title'] = $validated['name'];
        }
        if (empty($validated['price']) && !empty($validated['pvp'])) {
            $validated['price'] = $validated['pvp'];
        }

        $result = $this->duplicateDetector->checkDuplicate($validated);

        return response()->json([
            'status' => 'success',
            'message' => $result['has_potential_duplicate']
                ? 'Se detectaron coincidencias potenciales en el catalogo.'
                : 'No se detectaron duplicados en el catalogo.',
            'data' => $result,
        ]);
    }

    /**
     * Genera descripcion comercial y tags SEO con el Copiloto IA de Producto.
     */
    public function generateDescription(GenerateDescriptionRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $result = $this->aiCopilot->generateCommercialContent($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Contenido comercial y tags SEO generados exitosamente por el Copiloto IA.',
            'data' => $result,
        ]);
    }

    /**
     * Obtiene el detalle completo y metricas financieras de un producto por SKU (TASK-INV-18).
     */
    public function getSkuDetail(string $sku): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $cleanSku = trim(strtoupper($sku));
        $product = Product::with(['categories', 'variants.inventory', 'variants.optionValues'])
            ->where('sku', $cleanSku)
            ->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => "Producto con SKU {$cleanSku} no encontrado en el catalogo.",
            ], 404);
        }

        $meta = is_array($product->meta) ? $product->meta : [];
        $pvp = (float) $product->price;
        $cost = (float) $product->cost;
        $discountPct = (float) ($meta['discount_pct'] ?? 0);
        $regularPrice = (float) ($meta['regular_price'] ?? $pvp);
        $effectivePrice = $discountPct > 0 ? round($pvp * (1 - ($discountPct / 100)), 2) : $pvp;
        $cpp = (float) ($meta['weighted_average_cost'] ?? $cost);
        $unitProfit = max(0, $effectivePrice - $cpp);
        $marginPct = $effectivePrice > 0 ? round((($effectivePrice - $cpp) / $effectivePrice) * 100, 1) : 0.0;

        $sizes = $meta['sizes'] ?? [];
        if (empty($sizes) && $product->variants->isNotEmpty()) {
            foreach ($product->variants as $variant) {
                $attrs = is_array($variant->attributes) ? $variant->attributes : [];
                $sizeKey = strtolower($attrs['size'] ?? ($variant->sku ?: 'std'));
                $sizes[$sizeKey] = (int) ($variant->inventory?->quantity ?? 0);
            }
        }

        $variantIds = $product->variants->pluck('id')->filter()->all();
        $realLockedStock = !empty($variantIds)
            ? (int) \App\Models\StockLock::whereIn('product_variant_id', $variantIds)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->sum('quantity')
            : 0;
        $realCommittedStock = (int) $product->variants->sum(fn ($v) => $v->inventories->sum('reserved'));
        $realPhysicalStock = (int) $product->variants->sum(fn ($v) => $v->inventories->sum('quantity'));

        $physicalStock = $realPhysicalStock > 0 ? $realPhysicalStock : (int) ($meta['local_stock'] ?? (empty($sizes) ? ($product->stock ?? 0) : array_sum($sizes)));
        $committedStock = $realCommittedStock > 0 ? $realCommittedStock : (int) ($meta['committed_stock'] ?? 0);
        $lockedStock = $realLockedStock > 0 ? $realLockedStock : (int) ($meta['locked_stock'] ?? 0);
        $availableStock = max(0, $physicalStock - $committedStock - $lockedStock);

        $detail = [
            'sku' => $product->sku,
            'title' => $product->title,
            'name' => $product->title,
            'slug' => $product->slug,
            'brand' => $product->brand ?? 'Generico',
            'category' => $product->categories->first()?->name ?? ($meta['category'] ?? 'General'),
            'subcategory' => $meta['subcategory'] ?? '',
            'type' => ($product->type === 'physical' ? 'propio' : ($product->type ?? 'propio')),
            'is_published' => (bool) ($product->visible ?? true),
            'website_section' => $product->section_name ?? ($meta['section_name'] ?? ($meta['website_section'] ?? 'basicos')),
            'section_name' => $product->section_name ?? ($meta['section_name'] ?? ($meta['website_section'] ?? 'Línea Básicos Esenciales')),
            'description' => $product->description ?? '',
            'fabric_type' => $product->fabric ?? ($meta['fabric'] ?? ($meta['fabric_type'] ?? 'Algodon & Modal')),
            'fabric' => $product->fabric ?? ($meta['fabric'] ?? ($meta['fabric_type'] ?? 'Algodon & Modal')),
            'fabric_id' => $product->fabric_id ?? ($meta['fabric_id'] ?? null),
            'image' => $meta['image'] ?? null,
            'images' => $meta['images'] ?? (!empty($meta['gallery']) ? array_column($meta['gallery'], 'url') : (!empty($meta['image']) ? [$meta['image']] : [])),
            'gallery' => $meta['gallery'] ?? [],
            'color_name' => $meta['color_name'] ?? 'Negro Clasico',
            'color_hex' => $meta['color_hex'] ?? '#0f172a',
            'color_type' => $meta['color_type'] ?? 'solid',
            'design_type' => $meta['design_type'] ?? 'sin_diseno',
            'location' => $meta['location'] ?? 'A-01-A',
            'variant_dimension' => $meta['variant_dimension'] ?? 'size',
            'sizes' => $sizes,
            'shades' => $meta['shades'] ?? [],
            'volumes' => $meta['volumes'] ?? [],
            'stock' => [
                'local_stock' => $availableStock,
                'physical_stock' => $physicalStock,
                'committed_stock' => $committedStock,
                'locked_stock' => $lockedStock,
                'available_stock' => $availableStock,
            ],
            'financials' => [
                'pvp' => $pvp,
                'regular_price' => $regularPrice,
                'discount_pct' => $discountPct,
                'effective_price' => $effectivePrice,
                'cost' => $cost,
                'weighted_average_cost' => $cpp,
                'unit_profit' => $unitProfit,
                'margin_pct' => $marginPct,
                'margin_health' => $marginPct >= 40 ? 'healthy' : ($marginPct >= 25 ? 'acceptable' : 'risk'),
            ],
            'sanitary_control' => [
                'required' => (bool) ($meta['requires_sanitary_control'] ?? false),
                'batch_number' => $meta['batch_number'] ?? null,
                'expiration_date' => $meta['expiration_date'] ?? null,
                'pao_months' => (int) ($meta['pao_months'] ?? 12),
            ],
            'provider' => [
                'name' => $meta['provider'] ?? 'ModaIntima Textil MX',
                'url' => $meta['provider_url'] ?? '',
                'supplier_stock' => $meta['supplier_stock'] ?? null,
            ],
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Detalle de producto cargado exitosamente.',
            'data' => $detail,
        ]);
    }

    /**
     * Da de alta un nuevo producto comercial en el catalogo con sus variantes e inventario fisico inicial (TASK-INV-15 / SLICE 6).
     */
    public function storeProduct(StoreProductRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $cleanSku = trim(strtoupper($validated['sku']));
        $title = $validated['title'] ?? ($validated['name'] ?? 'Nuevo Producto');
        $approvalStatus = $validated['approval_status'] ?? $request->input('approval_status', 'approved');
        $isDraft = ($approvalStatus === 'draft');
        $rawType = $validated['type'] ?? 'physical';
        $dbType = ($rawType === 'propio') ? 'physical' : $rawType;

        $pvp = (float) $validated['pvp'];
        $cost = (float) $validated['cost'];
        $discountPct = (float) ($validated['discount_pct'] ?? 0);
        $regularPrice = (float) ($validated['regular_price'] ?? $pvp);
        $effectivePrice = $discountPct > 0 ? round($pvp * (1 - ($discountPct / 100)), 2) : $pvp;
        $cpp = isset($validated['weighted_average_cost']) ? (float) $validated['weighted_average_cost'] : $cost;
        $marginPct = $effectivePrice > 0 ? round((($effectivePrice - $cpp) / $effectivePrice) * 100, 1) : 0.0;

        $sizes = $validated['sizes'] ?? [];
        $localStock = 0;
        if (($dbType === 'physical' || $dbType === 'both') && is_array($sizes)) {
            foreach ($sizes as $sKey => $sVal) {
                if (is_array($sVal)) {
                    $localStock += max(0, (int) ($sVal['stock'] ?? $sVal['quantity'] ?? 0));
                } else {
                    $localStock += max(0, (int) $sVal);
                }
            }
        }

        // Procesar imágenes con variante de color de prenda (Regla 1 y 2)
        $rawImages = $validated['images'] ?? [];
        $imageColorMap = $request->input('images_by_color', $request->input('image_color_map', []));
        $formattedGallery = [];
        $primaryImageUrl = null;
        if (is_array($rawImages)) {
            foreach ($rawImages as $img) {
                if (is_string($img) && !empty($img)) {
                    $cName = $imageColorMap[$img] ?? ($validated['color_name'] ?? null);
                    $formattedGallery[] = [
                        'url' => $img,
                        'color_name' => $cName,
                        'is_primary' => empty($formattedGallery),
                    ];
                    if (!$primaryImageUrl) $primaryImageUrl = $img;
                } elseif (is_array($img) && !empty($img['url'])) {
                    $url = $img['url'];
                    $cName = $img['color_name'] ?? ($imageColorMap[$url] ?? ($validated['color_name'] ?? null));
                    $isPrim = !empty($img['is_primary']);
                    $formattedGallery[] = [
                        'url' => $url,
                        'color_name' => $cName,
                        'is_primary' => $isPrim,
                    ];
                    if ($isPrim || !$primaryImageUrl) {
                        $primaryImageUrl = $url;
                    }
                }
            }
        }

        // Resolver Fabric (Telas - Reglas 5 y 6)
        $fabricName = $validated['fabric'] ?? ($validated['fabric_type'] ?? null);
        $fabricId = $validated['fabric_id'] ?? null;
        if (!empty($fabricName) && empty($fabricId)) {
            $fabModel = \App\Models\ApparelFabric::where('name', $fabricName)->orWhere('slug', Str::slug($fabricName))->first();
            if ($fabModel) {
                $fabricId = $fabModel->id;
                $fabricName = $fabModel->name;
            }
        } elseif (!empty($fabricId) && empty($fabricName)) {
            $fabModel = \App\Models\ApparelFabric::find($fabricId);
            if ($fabModel) {
                $fabricName = $fabModel->name;
            }
        }

        $sectionName = $validated['section_name'] ?? ($validated['website_section'] ?? 'Línea Básicos Esenciales');

        $meta = [
            'category' => $validated['category'] ?? 'General',
            'subcategory' => $validated['subcategory'] ?? '',
            'regular_price' => $regularPrice,
            'discount_pct' => $discountPct,
            'effective_price' => $effectivePrice,
            'weighted_average_cost' => $cpp,
            'sizes' => $sizes,
            'shades' => $validated['shades'] ?? [],
            'volumes' => $validated['volumes'] ?? [],
            'local_stock' => $localStock,
            'location' => $validated['location'] ?? 'A-01-A',
            'fabric_type' => $fabricName ?? '',
            'fabric' => $fabricName ?? '',
            'fabric_id' => $fabricId,
            'color_name' => $validated['color_name'] ?? '',
            'color_hex' => $validated['color_hex'] ?? '#0f172a',
            'color_type' => $validated['color_type'] ?? 'solid',
            'design_type' => $validated['design_type'] ?? 'sin_diseno',
            'website_section' => $sectionName,
            'section_name' => $sectionName,
            'bullet_points' => $validated['bullet_points'] ?? [],
            'seo_tags' => $validated['seo_tags'] ?? [],
            'requires_sanitary_control' => (bool) ($validated['requires_sanitary_control'] ?? false),
            'batch_number' => $validated['batch_number'] ?? null,
            'expiration_date' => $validated['expiration_date'] ?? null,
            'pao_months' => $validated['pao_months'] ?? 12,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'provider' => $validated['provider'] ?? 'ModaIntima Textil MX',
            'provider_url' => $validated['provider_url'] ?? '',
            'images' => array_column($formattedGallery, 'url'),
            'gallery' => $formattedGallery,
            'images_by_color' => $imageColorMap ?: array_column($formattedGallery, 'color_name', 'url'),
            'image_color_map' => $imageColorMap ?: array_column($formattedGallery, 'color_name', 'url'),
            'image' => $primaryImageUrl,
            'approval_status' => $approvalStatus,
            'is_approved' => !$isDraft,
        ];

        return DB::transaction(function () use ($cleanSku, $title, $validated, $pvp, $cost, $dbType, $meta, $effectivePrice, $localStock, $marginPct, $sizes, $fabricName, $fabricId, $sectionName, $isDraft, $approvalStatus) {
            $product = Product::create([
                'sku' => $cleanSku,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . strtolower($cleanSku),
                'brand' => $validated['brand'] ?? 'Generico',
                'fabric' => $fabricName,
                'fabric_id' => $fabricId,
                'section_name' => $sectionName,
                'price' => $pvp,
                'cost' => $cost,
                'type' => $dbType,
                'visible' => $isDraft ? false : (isset($validated['is_published']) ? (bool) $validated['is_published'] : true),
                'description' => $validated['description'] ?? '',
                'meta' => $meta,
            ]);

            // Sincronización integral y automática en tiempo real (Familias, Categorías, Colores, Curvas de Tallas)
            $syncService = app(\App\Services\Catalog\ProductCatalogSyncService::class);
            $syncService->syncProduct($product, array_merge($validated, ['sizes' => $sizes]));

            $successMsg = $isDraft 
                ? "Producto {$cleanSku} pre-guardado como borrador (pendiente de aprobación)."
                : "Producto {$cleanSku} creado y aprobado exitosamente en el catálogo operativo.";

            return response()->json([
                'status' => 'success',
                'message' => $successMsg,
                'data' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'title' => $product->title,
                    'effective_price' => $effectivePrice,
                    'local_stock' => $localStock,
                    'margin_pct' => $marginPct,
                    'visible' => $product->visible,
                    'approval_status' => $approvalStatus,
                    'is_approved' => !$isDraft,
                    'created_at' => $product->created_at->toIso8601String(),
                ],
            ], 201);
        });
    }

    /**
     * Regla 5: Aprueba oficialmente un producto en estado de borrador para ingresarlo al catálogo operativo.
     */
    public function approveProduct(Request $request, int|string $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $product = is_numeric($id) ? Product::find((int) $id) : Product::where('sku', strtoupper(trim($id)))->first();
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Producto no encontrado para aprobación.'
            ], 404);
        }

        $meta = $product->meta ?? [];
        $meta['approval_status'] = 'approved';
        $meta['is_approved'] = true;
        $meta['approved_at'] = now()->toIso8601String();
        $meta['approved_by'] = auth()->id() ?? 'admin';

        $product->update([
            'visible' => true,
            'meta' => $meta,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Producto {$product->sku} aprobado y publicado oficialmente en el catálogo operativo.",
            'data' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'title' => $product->title,
                'visible' => $product->visible,
                'approval_status' => 'approved',
                'is_approved' => true,
            ]
        ]);
    }

    /**
     * Actualiza integralmente los datos comerciales, existencias y costos de un SKU (TASK-INV-18).
     */
    public function updateComplete(UpdateCompleteRequest $request, string $sku): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $cleanSku = trim(strtoupper($sku));
        $validated = $request->validated();

        $product = Product::where('sku', $cleanSku)->first();

        $rawType = $validated['type'] ?? ($product?->type ?? 'physical');
        $dbType = ($rawType === 'propio') ? 'physical' : $rawType;

        $title = $validated['title'] ?? ($validated['name'] ?? ($product?->title ?? 'Prenda de Catalogo'));
        $pvp = (float) $validated['pvp'];
        $cost = (float) $validated['cost'];
        $discountPct = (float) ($validated['discount_pct'] ?? 0);
        $regularPrice = (float) ($validated['regular_price'] ?? $pvp);
        $effectivePrice = $discountPct > 0 ? round($pvp * (1 - ($discountPct / 100)), 2) : $pvp;
        $cpp = isset($validated['weighted_average_cost']) ? (float) $validated['weighted_average_cost'] : $cost;
        $unitProfit = max(0, $effectivePrice - $cpp);
        $marginPct = $effectivePrice > 0 ? round((($effectivePrice - $cpp) / $effectivePrice) * 100, 1) : 0.0;

        $sizes = $validated['sizes'] ?? ($product?->meta['sizes'] ?? []);
        $localStock = array_sum(array_map('intval', $sizes));

        $existingMeta = $product && is_array($product->meta) ? $product->meta : [];

        // Resolver Fabric (Telas - Reglas 5 y 6)
        $fabricName = $validated['fabric'] ?? ($validated['fabric_type'] ?? null);
        if (empty($fabricName)) {
            $fabricName = $existingMeta['fabric'] ?? ($existingMeta['fabric_type'] ?? null);
        }

        $fabricId = null;
        if (!empty($fabricName)) {
            $fabModel = \App\Models\ApparelFabric::where('name', $fabricName)->orWhere('slug', Str::slug($fabricName))->first();
            if (!$fabModel) {
                $fabModel = \App\Models\ApparelFabric::create([
                    'name' => $fabricName,
                    'slug' => Str::slug($fabricName),
                    'description' => 'Fibra textil para indumentaria',
                    'is_active' => true,
                ]);
            }
            $fabricId = $fabModel->id;
            $fabricName = $fabModel->name;
        } elseif (!empty($validated['fabric_id'])) {
            $fabModel = \App\Models\ApparelFabric::find($validated['fabric_id']);
            if ($fabModel) {
                $fabricId = $fabModel->id;
                $fabricName = $fabModel->name;
            }
        }

        // Validación final de existencia de clave foránea
        if ($fabricId && !\App\Models\ApparelFabric::where('id', $fabricId)->exists()) {
            $fabricId = null;
        }

        $sectionName = $validated['section_name'] ?? ($validated['website_section'] ?? ($existingMeta['section_name'] ?? ($existingMeta['website_section'] ?? 'Línea Básicos Esenciales')));

        // Resolver Color y Hex Cromático (Regla 1)
        $colorName = $validated['color_name'] ?? ($existingMeta['color_name'] ?? 'Negro Clásico');
        $colorHex = $validated['color_hex'] ?? ($existingMeta['color_hex'] ?? null);
        $colorType = $validated['color_type'] ?? ($existingMeta['color_type'] ?? 'solid');

        if (empty($colorHex) || $colorHex === '#0f172a' || $colorHex === '#111827') {
            $foundColor = \App\Models\ApparelColor::where('name', $colorName)
                ->orWhere('name', 'like', '%' . explode(' ', $colorName)[0] . '%')
                ->first();
            if ($foundColor && $foundColor->hex_code) {
                $colorHex = $foundColor->hex_code;
                $colorType = $foundColor->color_type ?: $colorType;
            }
        }
        $colorHex = $colorHex ?: '#0f172a';

        // Procesar Imágenes con Variante de Color (Regla 1)
        $rawImages = $request->input('images', $existingMeta['gallery'] ?? ($existingMeta['images'] ?? []));
        $imageColorMap = $request->input('images_by_color', $request->input('image_color_map', []));
        $formattedGallery = [];
        $primaryImageUrl = $validated['image'] ?? ($existingMeta['image'] ?? null);

        if (is_array($rawImages)) {
            foreach ($rawImages as $img) {
                if (is_string($img) && !empty($img)) {
                    $cName = $imageColorMap[$img] ?? ($colorName ?? ($existingMeta['color_name'] ?? null));
                    $isPrim = ($img === $primaryImageUrl) || empty($formattedGallery);
                    $formattedGallery[] = [
                        'url' => $img,
                        'color_name' => $cName,
                        'is_primary' => $isPrim,
                    ];
                    if ($isPrim && empty($primaryImageUrl)) $primaryImageUrl = $img;
                } elseif (is_array($img) && !empty($img['url'])) {
                    $url = $img['url'];
                    $cName = $img['color_name'] ?? ($imageColorMap[$url] ?? ($colorName ?? ($existingMeta['color_name'] ?? null)));
                    $isPrim = !empty($img['is_primary']) || ($url === $primaryImageUrl);
                    $formattedGallery[] = [
                        'url' => $url,
                        'color_name' => $cName,
                        'is_primary' => $isPrim,
                    ];
                    if ($isPrim) $primaryImageUrl = $url;
                }
            }
        }
        if (empty($primaryImageUrl) && !empty($formattedGallery)) {
            $primaryImageUrl = $formattedGallery[0]['url'];
            $formattedGallery[0]['is_primary'] = true;
        }

        $newMeta = array_merge($existingMeta, [
            'category' => $validated['category'] ?? ($existingMeta['category'] ?? 'General'),
            'subcategory' => $validated['subcategory'] ?? ($existingMeta['subcategory'] ?? ''),
            'regular_price' => $regularPrice,
            'discount_pct' => $discountPct,
            'effective_price' => $effectivePrice,
            'weighted_average_cost' => $cpp,
            'sizes' => $sizes,
            'shades' => $validated['shades'] ?? ($existingMeta['shades'] ?? []),
            'volumes' => $validated['volumes'] ?? ($existingMeta['volumes'] ?? []),
            'local_stock' => $localStock,
            'location' => $validated['location'] ?? ($existingMeta['location'] ?? 'A-01-A'),
            'fabric_type' => $fabricName ?? ($existingMeta['fabric_type'] ?? 'Algodon & Modal'),
            'fabric' => $fabricName ?? '',
            'fabric_id' => $fabricId,
            'color_name' => $colorName,
            'color_hex' => $colorHex,
            'color_type' => $colorType,
            'design_type' => $validated['design_type'] ?? ($existingMeta['design_type'] ?? 'sin_diseno'),
            'website_section' => $sectionName,
            'section_name' => $sectionName,
            'requires_sanitary_control' => (bool) ($validated['requires_sanitary_control'] ?? ($existingMeta['requires_sanitary_control'] ?? false)),
            'batch_number' => $validated['batch_number'] ?? ($existingMeta['batch_number'] ?? null),
            'expiration_date' => $validated['expiration_date'] ?? ($existingMeta['expiration_date'] ?? null),
            'pao_months' => $validated['pao_months'] ?? ($existingMeta['pao_months'] ?? 12),
            'supplier_id' => $validated['supplier_id'] ?? ($existingMeta['supplier_id'] ?? null),
            'provider' => $validated['provider'] ?? ($existingMeta['provider'] ?? 'ModaIntima Textil MX'),
            'provider_url' => $validated['provider_url'] ?? ($existingMeta['provider_url'] ?? ''),
            'image' => $primaryImageUrl,
            'images' => array_column($formattedGallery, 'url'),
            'gallery' => $formattedGallery,
            'images_by_color' => $imageColorMap ?: array_column($formattedGallery, 'color_name', 'url'),
            'image_color_map' => $imageColorMap ?: array_column($formattedGallery, 'color_name', 'url'),
        ]);

        return DB::transaction(function () use ($product, $cleanSku, $title, $validated, $pvp, $cost, $dbType, $newMeta, $effectivePrice, $localStock, $marginPct, $fabricName, $fabricId, $sectionName) {
            if ($product) {
                $product->update([
                    'title' => $title,
                    'brand' => $validated['brand'] ?? $product->brand,
                    'fabric' => $fabricName,
                    'fabric_id' => $fabricId,
                    'section_name' => $sectionName,
                    'price' => $pvp,
                    'cost' => $cost,
                    'type' => $dbType,
                    'visible' => isset($validated['is_published']) ? (bool) $validated['is_published'] : $product->visible,
                    'description' => $validated['description'] ?? $product->description,
                    'meta' => $newMeta,
                ]);
            } else {
                $product = Product::create([
                    'sku' => $cleanSku,
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . strtolower($cleanSku),
                    'brand' => $validated['brand'] ?? 'Generico',
                    'fabric' => $fabricName,
                    'fabric_id' => $fabricId,
                    'section_name' => $sectionName,
                    'price' => $pvp,
                    'cost' => $cost,
                    'type' => $dbType,
                    'visible' => isset($validated['is_published']) ? (bool) $validated['is_published'] : true,
                    'description' => $validated['description'] ?? '',
                    'meta' => $newMeta,
                ]);
            }

            // Sincronización integral y automática en tiempo real (Familias, Categorías, Colores, Curvas de Tallas y Kardex)
            $syncService = app(\App\Services\Catalog\ProductCatalogSyncService::class);
            $syncService->syncProduct($product, $validated);

            return response()->json([
                'status' => 'success',
                'message' => "Producto {$cleanSku} actualizado correctamente en el catalogo.",
                'data' => [
                    'sku' => $product->sku,
                    'title' => $product->title,
                    'effective_price' => $effectivePrice,
                    'local_stock' => $localStock,
                    'margin_pct' => $marginPct,
                    'updated_at' => $product->updated_at->toIso8601String(),
                ],
            ]);
        });
    }
}
