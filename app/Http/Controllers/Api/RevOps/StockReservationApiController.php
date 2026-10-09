<?php

namespace App\Http\Controllers\Api\RevOps;

use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\Controller;
use App\Services\StockReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RevOps\ReleaseRequest;
use App\Http\Requests\Api\RevOps\ReserveRequest;
use Exception;

class StockReservationApiController extends Controller
{
    protected StockReservationService $reservationService;

    public function __construct(StockReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Bloquea stock para una variante durante el checkout activo (TASK-INT-06).
     */
    public function reserve(ReserveRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        try {
            $lock = $this->reservationService->reserveStock(
                $validated['cart_id'],
                $validated['variant_id'],
                $validated['quantity'] ?? 1,
                auth()->id(),
                $validated['duration_minutes'] ?? 15
            );

            return response()->json([
                'success' => true,
                'lock_id' => $lock->id,
                'cart_id' => $lock->cart_id,
                'expires_at' => $lock->expires_at->toIso8601String(),
                'status' => $lock->status,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Libera un bloqueo de stock cuando el usuario cancela o vacía su carrito.
     */
    public function release(ReleaseRequest $request): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $validated = $request->validated();

        $released = $this->reservationService->releaseStockLock($validated['lock_id']);

        return response()->json([
            'success' => $released,
            'message' => $released ? 'Reserva de stock liberada' : 'No se pudo liberar el candado',
        ]);
    }

    /**
     * Limpia los bloqueos de stock expirados.
     */
    public function cleanupExpired(): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $count = $this->reservationService->cleanupExpiredLocks();

        return response()->json([
            'success' => true,
            'locks_released' => $count,
        ]);
    }
}
