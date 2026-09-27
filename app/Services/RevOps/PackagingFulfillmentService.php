<?php

namespace App\Services\RevOps;

use App\Models\OperationalTask;
use App\Models\PackageType;
use App\Models\PackagingMovement;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PackagingFulfillmentService
{
    /**
     * Deduce de forma atomica e idempotente el insumo de empaque utilizado para un envio.
     */
    public function fulfillShipmentPackaging(Shipment $shipment, ?PackageType $packageType = null, ?User $user = null): ?PackagingMovement
    {
        // Idempotencia: evitar doble deduccion si ya fue descontado
        if ($shipment->packaging_fulfilled_at !== null) {
            return PackagingMovement::where('shipment_id', $shipment->id)->first();
        }

        // Si no se especifica el tipo de caja, resolver el paquete optimo segun peso volumetrico
        if (!$packageType) {
            $packageType = $this->resolveOptimalPackageType($shipment);
        }

        if (!$packageType) {
            return null;
        }

        return DB::transaction(function () use ($shipment, $packageType, $user) {
            $stockBefore = $packageType->stock_available;

            // Decrementar stock fisico del material de embalaje
            $packageType->decrement('stock_available', 1);
            $packageType->refresh();
            $stockAfter = $packageType->stock_available;

            // Registrar asiento inmutable en el Kardex de Insumos de Empaque
            $movement = PackagingMovement::create([
                'package_type_id' => $packageType->id,
                'shipment_id' => $shipment->id,
                'order_id' => $shipment->order_id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => 'CONSUMO_EMPAQUE_ENVIO',
                'quantity' => -1,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => "Consumo de insumo {$packageType->code} para envio {$shipment->tracking_number}."
            ]);

            // Actualizar envio con la caja consumida y marca de tiempo
            $shipment->update([
                'package_type_id' => $packageType->id,
                'packaging_fulfilled_at' => now(),
                'status' => in_array($shipment->status, ['label_created'], true) ? 'packed' : $shipment->status,
            ]);

            // Evaluar alerta ROP si el stock llego al punto de reorden
            $this->evaluateReorderPointAlert($packageType);

            return $movement;
        });
    }

    /**
     * Ajuste manual de existencias o punto de reorden con asiento en Kardex de empaque.
     */
    public function adjustStock(PackageType $packageType, int $newStock, ?float $unitCost = null, ?int $reorderPoint = null, ?User $user = null): PackagingMovement
    {
        return DB::transaction(function () use ($packageType, $newStock, $unitCost, $reorderPoint, $user) {
            $stockBefore = $packageType->stock_available;
            $diff = $newStock - $stockBefore;

            $updateData = ['stock_available' => $newStock];
            if ($unitCost !== null) {
                $updateData['unit_cost'] = $unitCost;
            }
            if ($reorderPoint !== null) {
                $updateData['reorder_point'] = $reorderPoint;
            }

            $packageType->update($updateData);

            $movement = PackagingMovement::create([
                'package_type_id' => $packageType->id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => $diff >= 0 ? 'ENTRADA_REABASTECIMIENTO' : 'AJUSTE_INVENTARIO',
                'quantity' => $diff,
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'notes' => "Ajuste manual de inventario de empaque por operador. Diferencia: {$diff} unidades."
            ]);

            // Reevaluar alerta ROP
            $this->evaluateReorderPointAlert($packageType);

            return $movement;
        });
    }

    /**
     * Deduce empaques para un lote de envios (ej. al cerrar manifiesto diario).
     */
    public function fulfillBatch(iterable $shipments, ?User $user = null): int
    {
        $deducted = 0;
        foreach ($shipments as $shipment) {
            if ($shipment->packaging_fulfilled_at === null) {
                $movement = $this->fulfillShipmentPackaging($shipment, null, $user);
                if ($movement) {
                    $deducted++;
                }
            }
        }

        return $deducted;
    }

    /**
     * Determina la caja adecuada segun el cubicaje del envio.
     */
    public function resolveOptimalPackageType(Shipment $shipment): ?PackageType
    {
        if ($shipment->package_type_id) {
            return PackageType::find($shipment->package_type_id);
        }

        $volWeight = (float)$shipment->weight_volumetric_kg;

        if ($volWeight > 0.8) {
            return PackageType::where('code', 'BOX-G03')->first()
                ?? PackageType::where('type', 'box')->orderByDesc('length_cm')->first();
        }

        if ($volWeight > 0.35) {
            return PackageType::where('code', 'BOX-M02')->first()
                ?? PackageType::where('type', 'box')->orderBy('length_cm')->skip(1)->first();
        }

        return PackageType::where('code', 'BOX-P01')->first()
            ?? PackageType::where('code', 'POLY-S01')->first()
            ?? PackageType::first();
    }

    /**
     * Genera o resuelve tareas operativas ROP de empaque segun el stock disponible.
     */
    protected function evaluateReorderPointAlert(PackageType $packageType): void
    {
        $rop = $packageType->reorder_point ?? 40;

        if ($packageType->stock_available <= $rop) {
            OperationalTask::firstOrCreate(
                [
                    'category' => 'compras',
                    'title' => "Alerta ROP: Stock critico de caja {$packageType->code}",
                    'status' => 'pending',
                ],
                [
                    'description' => "Quedan {$packageType->stock_available} unidades de {$packageType->name}. Punto de reorden alcanzado ({$rop} unidades). Emitir OC de insumos a cartonera.",
                    'priority' => 'urgente',
                    'due_at' => now()->addHours(4),
                    'action_type' => 'goto_inventory_purchases',
                    'action_payload' => [
                        'package_type_id' => $packageType->id,
                        'package_code' => $packageType->code,
                        'current_stock' => $packageType->stock_available,
                        'reorder_point' => $rop,
                    ],
                ]
            );
        } else {
            // Si el stock fue repuesto y supera el ROP, cerrar tareas pendientes de este empaque
            OperationalTask::where('category', 'compras')
                ->where('title', "Alerta ROP: Stock critico de caja {$packageType->code}")
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
        }
    }
}
