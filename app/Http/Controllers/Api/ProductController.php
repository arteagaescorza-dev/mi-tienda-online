<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Api\ProductResource;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('variants', 'categories')
            ->where('visible', true)
            ->paginate(12);

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $product->load('variants.optionValues.type', 'categories');

        return new ProductResource($product);
    }
}
