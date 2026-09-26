<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierCatalogSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierSyncController extends Controller
{
    public function store(Request $request, Supplier $supplier, SupplierCatalogSyncService $syncService): JsonResponse
    {
        $validated = $request->validate([
            'products' => ['required', 'array'],
            'products.*.supplier_sku' => ['required', 'string', 'max:255'],
            'products.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'products.*.supplier_url' => ['nullable', 'url', 'max:2048'],
            'products.*.price' => ['nullable', 'numeric', 'min:0'],
            'products.*.availability' => ['required', 'in:unknown,in_stock,out_of_stock'],
            'products.*.raw_snapshot' => ['nullable', 'array'],
        ]);

        $synced = $syncService->sync($supplier, $validated['products']);

        return response()->json(['synced' => $synced], 201);
    }
}