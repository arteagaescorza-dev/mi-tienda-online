<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\ApparelColor;
use App\Models\ApparelFabric;
use App\Models\CmsSection;
use App\Models\Supplier;
use App\Models\SupplierMoqSheet;
use App\Services\Inventory\VariantDimensionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $includeArchived = $request->boolean('include_archived', true);
        $this->dimensionService->ensureBootstrapDefaults();
        $curves = $this->dimensionService->getAllCurves($includeArchived);

        return response()->json([
            'status' => 'success',
            'data' => $curves,
        ]);
    }

    public function storeSizeCurve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category_scope' => 'nullable|string|max:50',
            'supplier_id' => 'nullable',
            'brand_id' => 'nullable',
            'description' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sizes' => 'nullable|array',
            'sizes.*.code' => 'required|string|max:20',
            'sizes.*.label' => 'nullable|string|max:100',
            'sizes.*.sort_order' => 'nullable|integer',
            'sizes.*.waist_min_cm' => 'nullable|numeric|min:0',
            'sizes.*.waist_max_cm' => 'nullable|numeric|min:0',
            'sizes.*.is_active' => 'nullable|boolean',
        ]);

        $curve = $this->dimensionService->createCurve($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Plantilla de curva de tallas creada correctamente.',
            'data' => $curve,
        ], 201);
    }

    public function updateSizeCurve(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'category_scope' => 'nullable|string|max:50',
            'supplier_id' => 'nullable',
            'brand_id' => 'nullable',
            'description' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $curve = $this->dimensionService->updateCurve($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Plantilla de curva de tallas actualizada correctamente.',
            'data' => $curve,
        ]);
    }

    public function toggleSizeCurveActive(int $id): JsonResponse
    {
        $curve = $this->dimensionService->toggleActiveCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la curva actualizado.',
            'data' => $curve,
        ]);
    }

    public function archiveSizeCurve(int $id): JsonResponse
    {
        $this->dimensionService->archiveCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Curva de tallas archivada correctamente.',
        ]);
    }

    public function restoreSizeCurve(int $id): JsonResponse
    {
        $curve = $this->dimensionService->restoreCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Curva de tallas restaurada correctamente.',
            'data' => $curve,
        ]);
    }

    public function destroySizeCurve(int $id): JsonResponse
    {
        $this->dimensionService->deleteCurve($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Curva de tallas eliminada definitivamente.',
        ]);
    }

    // =========================================================================
    // TALLAS INDIVIDUALES
    // =========================================================================

    public function storeSize(Request $request, int $curveId): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'label' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            'waist_min_cm' => 'nullable|numeric|min:0',
            'waist_max_cm' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $size = $this->dimensionService->addSizeToCurve($curveId, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Talla agregada a la curva exitosamente.',
            'data' => $size,
        ], 201);
    }

    public function updateSize(Request $request, int $sizeId): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:20',
            'label' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            'waist_min_cm' => 'nullable|numeric|min:0',
            'waist_max_cm' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $size = $this->dimensionService->updateSize($sizeId, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Talla actualizada correctamente.',
            'data' => $size,
        ]);
    }

    public function toggleSizeActive(int $sizeId): JsonResponse
    {
        $size = $this->dimensionService->toggleActiveSize($sizeId);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la talla actualizado.',
            'data' => $size,
        ]);
    }

    public function destroySize(int $sizeId): JsonResponse
    {
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
        $includeArchived = $request->boolean('include_archived', true);
        $this->dimensionService->ensureBootstrapDefaults();
        $volumes = $this->dimensionService->getAllVolumes($includeArchived);

        return response()->json([
            'status' => 'success',
            'data' => $volumes,
        ]);
    }

    public function storeFragranceVolume(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|numeric|min:0.1',
            'unit' => 'nullable|string|max:20',
            'label' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $volume = $this->dimensionService->createVolume($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen registrado correctamente.',
            'data' => $volume,
        ], 201);
    }

    public function updateFragranceVolume(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'sometimes|required|numeric|min:0.1',
            'unit' => 'nullable|string|max:20',
            'label' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $volume = $this->dimensionService->updateVolume($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen actualizado correctamente.',
            'data' => $volume,
        ]);
    }

    public function toggleFragranceVolumeActive(int $id): JsonResponse
    {
        $volume = $this->dimensionService->toggleActiveVolume($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del volumen actualizado.',
            'data' => $volume,
        ]);
    }

    public function archiveFragranceVolume(int $id): JsonResponse
    {
        $this->dimensionService->archiveVolume($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen archivado correctamente.',
        ]);
    }

    public function restoreFragranceVolume(int $id): JsonResponse
    {
        $volume = $this->dimensionService->restoreVolume($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Formato de volumen restaurado correctamente.',
            'data' => $volume,
        ]);
    }

    public function destroyFragranceVolume(int $id): JsonResponse
    {
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
        $includeArchived = $request->boolean('include_archived', true);
        $this->dimensionService->ensureBootstrapDefaults();
        $categories = $this->dimensionService->getAllCategories($includeArchived);

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'nullable|boolean',
        ]);

        $category = $this->dimensionService->createCategory($validated);

        return response()->json([
            'status' => 'success',
            'message' => empty($validated['parent_id']) ? 'Categoria principal creada correctamente.' : 'Subcategoria creada correctamente.',
            'data' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'slug' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $category = $this->dimensionService->updateCategory($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria actualizada correctamente.',
            'data' => $category,
        ]);
    }

    public function toggleCategoryActive(int $id): JsonResponse
    {
        $category = $this->dimensionService->toggleActiveCategory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la categoria actualizado.',
            'data' => $category,
        ]);
    }

    public function archiveCategory(int $id): JsonResponse
    {
        $this->dimensionService->archiveCategory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria archivada correctamente.',
        ]);
    }

    public function restoreCategory(int $id): JsonResponse
    {
        $category = $this->dimensionService->restoreCategory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria restaurada correctamente.',
            'data' => $category,
        ]);
    }

    public function destroyCategory(int $id): JsonResponse
    {
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

    public function storeFabric(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:80|unique:apparel_fabrics,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'El nombre del tipo de tela es obligatorio.',
            'name.unique' => 'Ya existe un tipo de tela registrado con este nombre.',
        ]);

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

    public function updateFabric(Request $request, int $id): JsonResponse
    {
        $fabric = ApparelFabric::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:80|unique:apparel_fabrics,name,' . $id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

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

    public function storeColor(Request $request): JsonResponse
    {
        if (strtolower(trim($request->input('name', ''))) === 'multicolor') {
            return response()->json([
                'status' => 'error',
                'message' => "El color 'Multicolor' es una opción predeterminada del sistema y no puede ser creado nuevamente.",
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:80|unique:apparel_colors,name',
            'hex_code' => 'nullable|string|max:250',
            'hex' => 'nullable|string|max:250',
            'color_type' => 'nullable|string|max:30',
            'type' => 'nullable|string|max:30',
            'gradient_css' => 'nullable|string|max:250',
        ], [
            'name.required' => 'El nombre del color es obligatorio.',
            'name.unique' => 'Este color ya se encuentra registrado.',
        ]);

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

    public function updateColor(Request $request, int $id): JsonResponse
    {
        $color = ApparelColor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:80|unique:apparel_colors,name,' . $id,
            'hex_code' => 'nullable|string|max:250',
            'hex' => 'nullable|string|max:250',
            'color_type' => 'nullable|string|max:30',
            'type' => 'nullable|string|max:30',
            'gradient_css' => 'nullable|string|max:250',
            'is_active' => 'nullable|boolean',
        ]);

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
        $sections = CmsSection::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sections,
        ]);
    }

    public function storeStoreSection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:500',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'universe' => 'nullable|string|max:32',
            'type' => 'nullable|string|max:60',
            'badge' => 'nullable|string|max:80',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|string|in:active,draft,archived',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'El nombre de la sección de tienda es obligatorio.',
        ]);

        $status = $validated['status'] ?? ($request->has('is_active') ? ($request->boolean('is_active') ? 'active' : 'draft') : 'active');
        $subtitle = $validated['subtitle'] ?? ($validated['description'] ?? null);
        $settings = [];
        if (!empty($validated['slug'])) {
            $settings['slug'] = trim($validated['slug']);
        }
        if (!empty($validated['description'])) {
            $settings['description'] = trim($validated['description']);
        }

        $section = CmsSection::create([
            'name' => trim($validated['name']),
            'title' => !empty($validated['title']) ? trim($validated['title']) : strtoupper(trim($validated['name'])),
            'subtitle' => $subtitle,
            'universe' => $validated['universe'] ?? 'all',
            'type' => $validated['type'] ?? 'catalog_section',
            'badge' => $validated['badge'] ?? 'E-Commerce',
            'query_rules' => ['section' => trim($validated['name']), 'slug' => $validated['slug'] ?? null],
            'settings' => $settings,
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Sección '{$section->name}' creada exitosamente.",
            'data' => $section,
        ], 201);
    }

    public function updateStoreSection(Request $request, int $id): JsonResponse
    {
        $section = CmsSection::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:120',
            'slug' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:500',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'universe' => 'nullable|string|max:32',
            'type' => 'nullable|string|max:60',
            'badge' => 'nullable|string|max:80',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|string|in:active,draft,archived',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['name'])) {
            $section->name = trim($validated['name']);
            $section->query_rules = array_merge($section->query_rules ?? [], ['section' => $section->name]);
        }
        if (array_key_exists('slug', $validated)) {
            $settings = $section->settings ?? [];
            $settings['slug'] = $validated['slug'];
            $section->settings = $settings;
            $qr = $section->query_rules ?? [];
            $qr['slug'] = $validated['slug'];
            $section->query_rules = $qr;
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
        SupplierMoqSheet::ensureTorzoDefault();

        $query = SupplierMoqSheet::with('supplier');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', '%' . $request->input('supplier_name') . '%');
        }

        $sheets = $query->orderByDesc('is_default')->orderBy('supplier_name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $sheets,
        ]);
    }

    public function storeMoqSheet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:150',
            'title' => 'required|string|max:200',
            'validity' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'tiers' => 'required|array|min:1',
            'tiers.*.key' => 'required|string|max:50',
            'tiers.*.label' => 'required|string|max:100',
            'tiers.*.min_qty' => 'required|integer|min:1',
            'tiers.*.color' => 'nullable|string|max:30',
            'tiers.*.badge_class' => 'nullable|string|max:100',
            'rows' => 'required|array|min:1',
            'rows.*.category' => 'required|string|max:150',
            'rows.*.prices' => 'required|array',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Regla: Los tabuladores MOQ se asocian directamente con proveedores creados previamente
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

        if (!empty($validated['is_default'])) {
            SupplierMoqSheet::where('supplier_id', $supplier->id)->update(['is_default' => false]);
        }

        $sheet = SupplierMoqSheet::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => "Tabulador de MOQ '{$sheet->title}' asociado exitosamente al proveedor '{$supplier->name}'.",
            'data' => $sheet->load('supplier'),
        ], 201);
    }

    public function updateMoqSheet(Request $request, int $id): JsonResponse
    {
        $sheet = SupplierMoqSheet::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:150',
            'title' => 'sometimes|required|string|max:200',
            'validity' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'tiers' => 'sometimes|required|array|min:1',
            'rows' => 'sometimes|required|array|min:1',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

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

        if (!empty($validated['is_default']) && $validated['is_default']) {
            $supId = $validated['supplier_id'] ?? $sheet->supplier_id;
            SupplierMoqSheet::where('id', '!=', $sheet->id)
                ->where('supplier_id', $supId)
                ->update(['is_default' => false]);
        }

        $sheet->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => "Tabulador de MOQ '{$sheet->title}' actualizado correctamente.",
            'data' => $sheet->fresh()->load('supplier'),
        ]);
    }

    public function toggleMoqSheetActive(int $id): JsonResponse
    {
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
        $sheet = SupplierMoqSheet::findOrFail($id);
        $title = $sheet->title;
        $sheet->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Tabulador de MOQ '{$title}' eliminado permanentemente.",
        ]);
    }
}
