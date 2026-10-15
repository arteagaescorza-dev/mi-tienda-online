<?php

namespace App\Http\Controllers\Api\RevOps;

use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\Controller;
use App\Models\ApparelColor;
use App\Models\ApparelFabric;
use App\Models\CmsSection;
use App\Models\Supplier;
use App\Models\SupplierMoqSheet;
use App\Services\Inventory\VariantDimensionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RevOps\UpdateMoqSheetRequest;
use App\Http\Requests\Api\RevOps\StoreMoqSheetRequest;
use App\Http\Requests\Api\RevOps\UpdateStoreSectionRequest;
use App\Http\Requests\Api\RevOps\StoreStoreSectionRequest;
use App\Http\Requests\Api\RevOps\UpdateColorRequest;
use App\Http\Requests\Api\RevOps\StoreColorRequest;
use App\Http\Requests\Api\RevOps\UpdateFabricRequest;
use App\Http\Requests\Api\RevOps\StoreFabricRequest;
use App\Http\Requests\Api\RevOps\UpdateCategoryRequest;
use App\Http\Requests\Api\RevOps\StoreCategoryRequest;
use App\Http\Requests\Api\RevOps\UpdateFragranceVolumeRequest;
use App\Http\Requests\Api\RevOps\StoreFragranceVolumeRequest;
use App\Http\Requests\Api\RevOps\UpdateSizeRequest;
use App\Http\Requests\Api\RevOps\StoreSizeRequest;
use App\Http\Requests\Api\RevOps\UpdateSizeCurveRequest;
use App\Http\Requests\Api\RevOps\StoreSizeCurveRequest;
use Illuminate\Support\Str;

class InventorySettingsApiController extends Controller
{
    public function __construct(
        protected VariantDimensionService $dimensionService
    ) {}

    // =========================================================================
    // CURVAS DE TALLAS
    // =========================================================================

    public function getSizeCurves(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $includeArchived = $request->boolean('include_archived', true);
        $this->dimensionService->ensureBootstrapDefaults();
        $curves = $this->dimensionService->getAllCurves($includeArchived);

        return response()->json([
            'status' => 'success',
            'data' => $curves,
        ]);
    }

    public function storeSizeCurve(StoreSizeCurveRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $curve = $this->dimensionService->createCurve($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Plantilla de curva de tallas creada correctamente.',
            'data' => $curve,
        ], 201);
    }

    public function updateSizeCurve(UpdateSizeCurveRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $curve = $this->dimensionService->updateCurve($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Plantilla de curva de tallas actualizada correctamente.',
            'data' => $curve,
        ]);
    }

    public function toggleSizeCurveActive(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $curve = $this->dimensionService->toggleActiveCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la curva actualizado.',
            'data' => $curve,
        ]);
    }

    public function archiveSizeCurve(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $this->dimensionService->archiveCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Curva de tallas archivada correctamente.',
        ]);
    }

    public function restoreSizeCurve(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $curve = $this->dimensionService->restoreCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Curva de tallas restaurada correctamente.',
            'data' => $curve,
        ]);
    }

    public function destroySizeCurve(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $this->dimensionService->deleteCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Curva de tallas eliminada definitivamente.',
        ]);
    }

    // =========================================================================
    // TALLAS INDIVIDUALES
    // =========================================================================

    public function storeSize(StoreSizeRequest $request, int $curveId): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $size = $this->dimensionService->addSizeToCurve($curveId, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Talla agregada a la curva exitosamente.',
            'data' => $size,
        ], 201);
    }

    public function updateSize(UpdateSizeRequest $request, int $sizeId): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $size = $this->dimensionService->updateSize($sizeId, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Talla actualizada correctamente.',
            'data' => $size,
        ]);
    }

    public function toggleSizeActive(int $sizeId): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $size = $this->dimensionService->toggleActiveSize($sizeId);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la talla actualizado.',
            'data' => $size,
        ]);
    }

    public function destroySize(int $sizeId): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $this->dimensionService->deleteSize($sizeId);

        return response()->json([
            'status' => 'success',
            'message' => 'Talla eliminada de la curva.',
        ]);
    }

    // =========================================================================
    // VOLUMENES DE PERFUMERIA
    // =========================================================================

    public function getFragranceVolumes(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $includeArchived = $request->boolean('include_archived', true);
        $this->dimensionService->ensureBootstrapDefaults();
        $volumes = $this->dimensionService->getAllVolumes($includeArchived);

        return response()->json([
            'status' => 'success',
            'data' => $volumes,
        ]);
    }

    public function storeFragranceVolume(StoreFragranceVolumeRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $volume = $this->dimensionService->createVolume($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen registrado correctamente.',
            'data' => $volume,
        ], 201);
    }

    public function updateFragranceVolume(UpdateFragranceVolumeRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $volume = $this->dimensionService->updateVolume($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen actualizado correctamente.',
            'data' => $volume,
        ]);
    }

    public function toggleFragranceVolumeActive(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $volume = $this->dimensionService->toggleActiveVolume($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del volumen actualizado.',
            'data' => $volume,
        ]);
    }

    public function archiveFragranceVolume(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $this->dimensionService->archiveVolume($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen archivado correctamente.',
        ]);
    }

    public function restoreFragranceVolume(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $volume = $this->dimensionService->restoreVolume($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen restaurado correctamente.',
            'data' => $volume,
        ]);
    }

    public function destroyFragranceVolume(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $this->dimensionService->deleteVolume($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen eliminado definitivamente.',
        ]);
    }

    // =========================================================================
    // CATEGORIAS Y SUBCATEGORIAS
    // =========================================================================

    public function getCategories(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $includeArchived = $request->boolean('include_archived', true);
        $this->dimensionService->ensureBootstrapDefaults();
        $categories = $this->dimensionService->getAllCategories($includeArchived);

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    public function storeCategory(StoreCategoryRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $category = $this->dimensionService->createCategory($validated);

        return response()->json([
            'status' => 'success',
            'message' => empty($validated['parent_id']) ? 'Categoria principal creada correctamente.' : 'Subcategoria creada correctamente.',
            'data' => $category,
        ], 201);
    }

    public function updateCategory(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $category = $this->dimensionService->updateCategory($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria actualizada correctamente.',
            'data' => $category,
        ]);
    }

    public function toggleCategoryActive(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $category = $this->dimensionService->toggleActiveCategory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la categoria actualizado.',
            'data' => $category,
        ]);
    }

    public function archiveCategory(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $this->dimensionService->archiveCategory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria archivada correctamente.',
        ]);
    }

    public function restoreCategory(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $category = $this->dimensionService->restoreCategory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria restaurada correctamente.',
            'data' => $category,
        ]);
    }

    public function destroyCategory(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $this->dimensionService->deleteCategory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria eliminada definitivamente.',
        ]);
    }

    // =========================================================================
    // TELAS & FIBRAS TEXTILES (CRUD COMPLETO - REGLAS 5 Y 6)
    // =========================================================================

    public function getFabrics(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $query = ApparelFabric::query();
        if (!$request->boolean('include_archived', true)) {
            $query->where('is_active', true);
        }
        $fabrics = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $fabrics,
        ]);
    }

    public function storeFabric(StoreFabricRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (ApparelFabric::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $fabric = ApparelFabric::create([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Tipo de tela '{$fabric->name}' guardado correctamente.",
            'data' => $fabric,
        ], 201);
    }

    public function updateFabric(UpdateFabricRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $fabric = ApparelFabric::findOrFail($id);

        $validated = $request->validated();

        if (!empty($validated['name'])) {
            $fabric->name = trim($validated['name']);
            $fabric->slug = Str::slug($fabric->name);
        }
        if (array_key_exists('description', $validated)) {
            $fabric->description = $validated['description'];
        }
        if (array_key_exists('is_active', $validated)) {
            $fabric->is_active = (bool) $validated['is_active'];
        }
        $fabric->save();

        return response()->json([
            'status' => 'success',
            'message' => "Tipo de tela '{$fabric->name}' actualizado correctamente.",
            'data' => $fabric,
        ]);
    }

    public function toggleFabricActive(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $fabric = ApparelFabric::findOrFail($id);
        $fabric->is_active = !$fabric->is_active;
        $fabric->save();

        return response()->json([
            'status' => 'success',
            'message' => "Estado de la tela '{$fabric->name}' actualizado.",
            'data' => $fabric,
        ]);
    }

    public function archiveFabric(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $fabric = ApparelFabric::findOrFail($id);
        $fabric->is_active = false;
        $fabric->save();

        return response()->json([
            'status' => 'success',
            'message' => "Tipo de tela '{$fabric->name}' archivado correctamente.",
            'data' => $fabric,
        ]);
    }

    public function restoreFabric(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $fabric = ApparelFabric::findOrFail($id);
        $fabric->is_active = true;
        $fabric->save();

        return response()->json([
            'status' => 'success',
            'message' => "Tipo de tela '{$fabric->name}' restaurado.",
            'data' => $fabric,
        ]);
    }

    public function destroyFabric(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $fabric = ApparelFabric::findOrFail($id);
        $name = $fabric->name;
        $fabric->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Tipo de tela '{$name}' eliminado permanentemente.",
        ]);
    }

    // =========================================================================
    // COLORES DE PRENDA & SWATCHES (ALTA IN SITU - REGLA 3)
    // =========================================================================

    public function getColors(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $colors = ApparelColor::orderBy('name')->get();
        if (!$colors->contains(fn($c) => strtolower($c->name) === 'multicolor')) {
            ApparelColor::firstOrCreate(
                ['name' => 'Multicolor'],
                [
                    'color_type' => 'gradient',
                    'hex_code' => '#ef4444',
                    'gradient_css' => 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)',
                    'is_active' => true,
                ]
            );
            $colors = ApparelColor::orderBy('name')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $colors,
        ]);
    }

    public function storeColor(StoreColorRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        if (strtolower(trim($request->input('name', ''))) === 'multicolor') {
            return response()->json([
                'status' => 'error',
                'message' => "El color 'Multicolor' es una opción predeterminada del sistema y no puede ser creado nuevamente.",
            ], 422);
        }

        $validated = $request->validated();

        $rawHex = $request->input('hex_code', $request->input('hex', '#000000'));
        $colorType = $request->input('color_type', $request->input('type', 'solid'));
        $gradientCss = $request->input('gradient_css');

        if (str_starts_with($rawHex, 'linear-gradient')) {
            $gradientCss = $rawHex;
            $hex = '#6366f1';
            $colorType = $colorType === 'solid' ? 'iridescent' : $colorType;
        } else {
            $hex = str_starts_with($rawHex, '#') ? $rawHex : '#' . $rawHex;
        }

        $color = ApparelColor::create([
            'name' => trim($validated['name']),
            'hex_code' => $hex,
            'color_type' => $colorType,
            'gradient_css' => $gradientCss,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Color de prenda '{$color->name}' registrado exitosamente.",
            'data' => array_merge($color->toArray(), [
                'type' => $color->color_type,
                'hex' => $color->gradient_css ?: $color->hex_code,
            ]),
        ], 201);
    }

    public function updateColor(UpdateColorRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $color = ApparelColor::findOrFail($id);

        $validated = $request->validated();

        if (!empty($validated['name'])) $color->name = trim($validated['name']);
        
        $rawHex = $request->input('hex_code', $request->input('hex'));
        if ($rawHex) {
            if (str_starts_with($rawHex, 'linear-gradient')) {
                $color->gradient_css = $rawHex;
            } else {
                $color->hex_code = str_starts_with($rawHex, '#') ? $rawHex : '#' . $rawHex;
            }
        }
        if ($request->has('color_type') || $request->has('type')) {
            $color->color_type = $request->input('color_type', $request->input('type'));
        }
        if ($request->has('gradient_css')) $color->gradient_css = $request->input('gradient_css');
        if (isset($validated['is_active'])) $color->is_active = (bool) $validated['is_active'];
        $color->save();

        return response()->json([
            'status' => 'success',
            'message' => "Color '{$color->name}' actualizado.",
            'data' => $color,
        ]);
    }

    public function toggleColorActive(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $color = ApparelColor::findOrFail($id);
        $color->is_active = !$color->is_active;
        $color->save();

        return response()->json([
            'status' => 'success',
            'message' => "Estado del color actualizado.",
            'data' => $color,
        ]);
    }

    public function destroyColor(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $color = ApparelColor::findOrFail($id);
        if (strtolower($color->name) === 'multicolor') {
            return response()->json([
                'status' => 'error',
                'message' => "El color base del sistema 'Multicolor' no puede ser eliminado.",
            ], 422);
        }
        $name = $color->name;
        $color->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Color '{$name}' eliminado.",
        ]);
    }

    // =========================================================================
    // SECCIONES DEL SITIO WEB E-COMMERCE (CRUD COMPLETO - REGLA 7)
    // =========================================================================

    public function getStoreSections(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $sections = CmsSection::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sections,
        ]);
    }

    public function storeStoreSection(StoreStoreSectionRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $status = $validated['status'] ?? ($request->has('is_active') ? ($request->boolean('is_active') ? 'active' : 'draft') : 'active');
        $subtitle = $validated['subtitle'] ?? ($validated['description'] ?? null);
        $settings = [];
        if (!empty($validated['slug'])) {
            $settings['slug'] = trim($validated['slug']);
        }
        if (!empty($validated['description'])) {
            $settings['description'] = trim($validated['description']);
        }

        $queryRules = $validated['query_rules'] ?? [];
        $queryRules['section'] = trim($validated['name']);
        if (!empty($validated['slug'])) {
            $queryRules['slug'] = trim($validated['slug']);
        }
        if (isset($validated['is_new'])) {
            $queryRules['is_new'] = (bool) $validated['is_new'];
        }
        if (isset($validated['has_discount'])) {
            $queryRules['has_discount'] = (bool) $validated['has_discount'];
        }
        if (isset($validated['fabric_id'])) {
            $queryRules['fabric_id'] = !empty($validated['fabric_id']) ? (int) $validated['fabric_id'] : null;
        }
        if (isset($validated['category_id'])) {
            $queryRules['category_id'] = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
        }
        if (isset($validated['subcategory_id'])) {
            $queryRules['subcategory_id'] = !empty($validated['subcategory_id']) ? (int) $validated['subcategory_id'] : null;
        }

        $maxSort = CmsSection::max('sort_order') ?? 0;
        $sortOrder = $validated['sort_order'] ?? ($maxSort + 1);

        $section = CmsSection::create([
            'name' => trim($validated['name']),
            'title' => !empty($validated['title']) ? trim($validated['title']) : strtoupper(trim($validated['name'])),
            'subtitle' => $subtitle,
            'universe' => $validated['universe'] ?? 'all',
            'type' => $validated['type'] ?? 'catalog_section',
            'badge' => $validated['badge'] ?? 'E-Commerce',
            'query_rules' => $queryRules,
            'settings' => $settings,
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Sección '{$section->name}' creada exitosamente.",
            'data' => $section,
        ], 201);
    }

    public function updateStoreSection(UpdateStoreSectionRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $section = CmsSection::findOrFail($id);

        $validated = $request->validated();
        $qr = $section->query_rules ?? [];

        if (!empty($validated['name'])) {
            $section->name = trim($validated['name']);
            $qr['section'] = $section->name;
        }
        if (array_key_exists('slug', $validated)) {
            $settings = $section->settings ?? [];
            $settings['slug'] = $validated['slug'];
            $section->settings = $settings;
            $qr['slug'] = $validated['slug'];
        }
        if (array_key_exists('description', $validated)) {
            $section->subtitle = $validated['description'];
            $settings = $section->settings ?? [];
            $settings['description'] = $validated['description'];
            $section->settings = $settings;
        }
        if (array_key_exists('is_active', $validated)) {
            $section->status = $validated['is_active'] ? 'active' : 'draft';
        }
        if (array_key_exists('is_new', $validated)) {
            $qr['is_new'] = (bool) $validated['is_new'];
        }
        if (array_key_exists('has_discount', $validated)) {
            $qr['has_discount'] = (bool) $validated['has_discount'];
        }
        if (array_key_exists('fabric_id', $validated)) {
            $qr['fabric_id'] = !empty($validated['fabric_id']) ? (int) $validated['fabric_id'] : null;
        }
        if (array_key_exists('category_id', $validated)) {
            $qr['category_id'] = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
        }
        if (array_key_exists('subcategory_id', $validated)) {
            $qr['subcategory_id'] = !empty($validated['subcategory_id']) ? (int) $validated['subcategory_id'] : null;
        }
        if (array_key_exists('query_rules', $validated) && is_array($validated['query_rules'])) {
            $qr = array_merge($qr, $validated['query_rules']);
        }
        $section->query_rules = $qr;

        if (array_key_exists('title', $validated)) $section->title = $validated['title'];
        if (array_key_exists('subtitle', $validated)) $section->subtitle = $validated['subtitle'];
        if (array_key_exists('universe', $validated)) $section->universe = $validated['universe'];
        if (array_key_exists('type', $validated)) $section->type = $validated['type'];
        if (array_key_exists('badge', $validated)) $section->badge = $validated['badge'];
        if (array_key_exists('sort_order', $validated)) $section->sort_order = $validated['sort_order'];
        if (array_key_exists('status', $validated)) $section->status = $validated['status'];

        $section->save();

        return response()->json([
            'status' => 'success',
            'message' => "Sección '{$section->name}' actualizada correctamente.",
            'data' => $section,
        ]);
    }

    public function toggleStoreSectionActive(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $section = CmsSection::findOrFail($id);
        $section->status = ($section->status === 'active') ? 'draft' : 'active';
        $section->save();

        return response()->json([
            'status' => 'success',
            'message' => "Estado de la sección '{$section->name}' conmutado a '{$section->status}'.",
            'data' => $section,
        ]);
    }

    public function archiveStoreSection(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $section = CmsSection::findOrFail($id);
        $section->status = 'archived';
        $section->save();

        return response()->json([
            'status' => 'success',
            'message' => "Sección '{$section->name}' archivada.",
            'data' => $section,
        ]);
    }

    public function restoreStoreSection(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $section = CmsSection::findOrFail($id);
        $section->status = 'active';
        $section->save();

        return response()->json([
            'status' => 'success',
            'message' => "Sección '{$section->name}' restaurada a activa.",
            'data' => $section,
        ]);
    }

    public function destroyStoreSection(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $section = CmsSection::findOrFail($id);
        $name = $section->name;
        $section->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Sección '{$name}' eliminada permanentemente.",
        ]);
    }

    // =========================================================================
    // TABULADORES DE MOQ POR PROVEEDOR (REGLA COMPRAS & INVENTARIO)
    // =========================================================================

    public function getMoqSheets(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        if (SupplierMoqSheet::count() === 0) {
            SupplierMoqSheet::ensureTorzoDefault();
        }

        $query = SupplierMoqSheet::with('supplier');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', '%' . $request->input('supplier_name') . '%');
        }

        $sheets = $query->orderBy('supplier_name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $sheets,
        ]);
    }

    public function storeMoqSheet(StoreMoqSheetRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        // Regla: Los tabuladores MOQ son independientes y se asocian obligatoriamente a un proveedor registrado
        $supplier = null;
        if (!empty($validated['supplier_id'])) {
            $supplier = Supplier::find($validated['supplier_id']);
        } elseif (!empty($validated['supplier_name'])) {
            $supplier = Supplier::where('name', $validated['supplier_name'])->first();
        }

        if (!$supplier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Regla de negocio: El tabulador MOQ debe asociarse obligatoriamente a un proveedor previamente registrado en el sistema.',
            ], 422);
        }

        $validated['supplier_id'] = $supplier->id;
        $validated['supplier_name'] = $supplier->name;
        $validated['is_default'] = false;

        $existing = SupplierMoqSheet::where('supplier_id', $supplier->id)->first();
        if ($existing) {
            $existing->update($validated);
            $sheet = $existing;
        } else {
            $sheet = SupplierMoqSheet::create($validated);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Tabulador de MOQ '{$sheet->title}' guardado exitosamente para el proveedor '{$supplier->name}'.",
            'data' => $sheet->load('supplier'),
        ], 201);
    }

    public function updateMoqSheet(UpdateMoqSheetRequest $request, int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $sheet = SupplierMoqSheet::findOrFail($id);

        $validated = $request->validated();

        if (!empty($validated['supplier_id'])) {
            $supplier = Supplier::find($validated['supplier_id']);
            if ($supplier) {
                $validated['supplier_name'] = $supplier->name;
            }
        } elseif (!empty($validated['supplier_name'])) {
            $supplier = Supplier::where('name', $validated['supplier_name'])->first();
            if ($supplier) {
                $validated['supplier_id'] = $supplier->id;
            }
        }

        $validated['is_default'] = false;
        $sheet->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => "Tabulador de MOQ '{$sheet->title}' actualizado correctamente.",
            'data' => $sheet->fresh()->load('supplier'),
        ]);
    }

    public function toggleMoqSheetActive(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $sheet = SupplierMoqSheet::findOrFail($id);
        $sheet->is_active = !$sheet->is_active;
        $sheet->save();

        return response()->json([
            'status' => 'success',
            'message' => "Estado del tabulador '{$sheet->title}' actualizado a " . ($sheet->is_active ? 'Activo' : 'Inactivo') . '.',
            'data' => $sheet,
        ]);
    }

    public function destroyMoqSheet(int $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $sheet = SupplierMoqSheet::findOrFail($id);
        $title = $sheet->title;
        $sheet->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Tabulador de MOQ '{$title}' eliminado permanentemente.",
        ]);
    }
}
