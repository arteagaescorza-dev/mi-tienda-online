<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Services\Inventory\DuplicateDetectionService;
use App\Services\Inventory\SkuGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSkuAndDuplicateApiController extends Controller
{
    public function __construct(
        protected SkuGeneratorService $skuGenerator,
        protected DuplicateDetectionService $duplicateDetector
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

    /**
     * Evalua posibles productos duplicados en el catalogo previo al guardado.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer',
            'price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'exclude_product_id' => 'nullable|integer',
            'threshold_warning' => 'nullable|integer|min:1|max:100',
            'threshold_match' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->duplicateDetector->checkDuplicate($validated);

        return response()->json([
            'status' => 'success',
            'message' => $result['has_potential_duplicate']
                ? 'Se detectaron coincidencias potenciales en el catalogo.'
                : 'No se detectaron duplicados en el catalogo.',
            'data' => $result,
        ]);
    }
}
