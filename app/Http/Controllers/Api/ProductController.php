<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Api\ProductResource;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Lista los productos del catálogo maestro (requiere rol de manager de catálogo).
     */
    public function index(Request $request)
    {
        Gate::authorize('manage-catalog');

        $products = Product::with('variants', 'categories')
            ->where('visible', true)
            ->paginate(12);

        return ProductResource::collection($products);
    }

    /**
     * Muestra el detalle de un producto específico.
     */
    public function show(Product $product)
    {
        Gate::authorize('manage-catalog');

        $product->load('variants.optionValues.type', 'categories');

        return new ProductResource($product);
    }
}
