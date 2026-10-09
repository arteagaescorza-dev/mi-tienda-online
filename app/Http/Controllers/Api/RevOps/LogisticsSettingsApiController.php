<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\RevOps\UpdateLogisticsSettingsRequest;
use Illuminate\Support\Facades\Gate;

class LogisticsSettingsApiController extends Controller
{
    /**
     * Retrieve current logistics settings
     */
    public function index(): JsonResponse
    {
        Gate::authorize('manage-logistics');

        // Emulating a settings retrieval, normally from a Settings model or Cache
        return response()->json([
            'data' => [
                'rma_retention_days' => config('logistics.rma_retention_days', 30),
            ]
        ]);
    }

    /**
     * Update logistics settings, like the RMA retention policy.
     */
    public function update(UpdateLogisticsSettingsRequest $request): JsonResponse
    {
        Gate::authorize('manage-logistics');

        $validated = $request->validated();

        // Emulating saving to a Settings table/provider
        // Settings::set('logistics.rma_retention_days', $validated['rma_retention_days']);

        return response()->json([
            'message' => 'Ajustes de logística actualizados correctamente.',
            'data' => $validated
        ]);
    }
}

