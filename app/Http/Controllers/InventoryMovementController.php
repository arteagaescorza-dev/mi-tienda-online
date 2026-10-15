<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use App\Http\Requests\Portal\Inventory\InventoryMovementStoreRequest;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InventoryMovementController extends Controller
{
    public function store(InventoryMovementStoreRequest $request, Inventory $inventory): Response
    {
        Gate::authorize('manage-inventory');

        $validated = $request->validated();

        $movement = DB::transaction(function () use ($request, $inventory, $validated): InventoryMovement {
            $lockedInventory = Inventory::query()->lockForUpdate()->findOrFail($inventory->id);
            $delta = $validated['type'] === 'increase' ? $validated['quantity'] : -$validated['quantity'];
            $available = $lockedInventory->quantity - $lockedInventory->reserved;

            if ($delta < 0 && $available < abs($delta)) {
                $this->reject('La salida supera el stock disponible.');
            }

            $before = $lockedInventory->quantity;
            $lockedInventory->increment('quantity', $delta);

            return InventoryMovement::create([
                'inventory_id' => $lockedInventory->id,
                'user_id' => $request->user()->id,
                'quantity' => $delta,
                'quantity_before' => $before,
                'quantity_after' => $before + $delta,
                'reason' => $validated['reason'],
            ]);
        });

        if (! $request->expectsJson()) {
            return redirect()
                ->route('portal.inventory')
                ->with('success', 'Movimiento de inventario registrado correctamente.');
        }

        return response()->json(['movement' => $movement], 201);
    }

    private function reject(string $message): never
    {
        if (! request()->expectsJson()) {
            throw new HttpResponseException(redirect()
                ->route('portal.inventory')
                ->withErrors(['quantity' => $message]));
        }

        throw new HttpResponseException(response()->json([
            'message' => $message,
            'errors' => ['quantity' => [$message]],
        ], 422));
    }
}