<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Services\Inventory\VariantDimensionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
