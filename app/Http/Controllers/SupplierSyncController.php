<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierCatalogSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Portal\B2b\SupplierSyncStoreRequest;
use Illuminate\Support\Facades\Gate;


class SupplierSyncController extends Controller
{
    public function store(SupplierSyncStoreRequest $request, Supplier $supplier, SupplierCatalogSyncService $syncService): JsonResponse
    {
        Gate::authorize('manage-b2b');

        $validated = $request->validated();

        $synced = $syncService->sync($supplier, $validated['products']);

        return response()->json(['synced' => $synced], 201);
    }
}