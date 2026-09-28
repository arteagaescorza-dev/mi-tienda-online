<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Services\Inventory\DuplicateDetectionService;
use App\Services\Inventory\ProductAiCopilotService;
use App\Services\Inventory\SkuGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function generateSku(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'nullable',
            'brand' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:255',
            'variant_code' => 'nullable',
            'variant' => 'nullable',
            'prefix_override' => 'nullable|string|max:10',
        ]);

        $result = $this->skuGenerator->generateSku($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Codigo SKU generado correctamente.',
            'data' => $result,
        ]);
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer',
            'category' => 'nullable',
            'price' => 'nullable|numeric|min:0',
            'pvp' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'exclude_product_id' => 'nullable|integer',
            'threshold_warning' => 'nullable|integer|min:1|max:100',
            'threshold_match' => 'nullable|integer|min:1|max:100',
        ]);

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
    public function generateDescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'fabric_type' => 'nullable|string|max:100',
            'color_name' => 'nullable|string|max:100',
            'design_type' => 'nullable|string|max:50',
            'target_audience' => 'nullable|string|max:50',
            'pvp' => 'nullable|numeric|min:0',
        ]);

        $result = $this->aiCopilot->generateCommercialContent($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Contenido comercial y tags SEO generados exitosamente por el Copiloto IA.',
            'data' => $result,
        ]);
    }
}
