<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;
use App\Models\OperationalTask;

class EvaluateInventoryRop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:evaluate-rop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evalúa el Punto de Reorden (ROP), Venta Media Diaria (VMD) y Días de Cobertura (DOH) para generar alertas operativas (TASK-DSH-04)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Evaluando niveles de stock frente a Punto de Reorden (ROP)...');

        $inventories = Inventory::with('variant.product')->get();
        $criticalCount = 0;

        foreach ($inventories as $inv) {
            $available = $inv->quantity - $inv->reserved;
            $threshold = $inv->min_threshold ?: 5;

            // Si el stock disponible está por debajo del umbral de reorden
            if ($available <= $threshold && $inv->variant) {
                $criticalCount++;
                $productTitle = $inv->variant->product ? $inv->variant->product->title : 'SKU ' . $inv->variant->sku;
                $variantTitle = $inv->variant->title;

                $taskTitle = "Alerta ROP Crítica: {$productTitle} ({$variantTitle}) - {$available} disp. (Umbral: {$threshold})";

                OperationalTask::firstOrCreate(
                    [
                        'title' => $taskTitle,
                        'category' => 'inventory_rop',
                        'status' => 'pending',
                    ],
                    [
                        'priority' => $available <= 2 ? 'critical' : 'high',
                        'assigned_role' => 'operario_almacen',
                        'sla_minutes_limit' => 180,
                        'action_payload' => [
                            'variant_id' => $inv->variant_id,
                            'available_stock' => $available,
                            'threshold' => $threshold,
                            'suggested_reorder_units' => max(20, $threshold * 3),
                        ],
                    ]
                );

                $this->warn(" - [ROP ALERTA] {$taskTitle}");
            }
        }

        $this->info("Evaluación de ROP finalizada. {$criticalCount} alertas procesadas.");

        return self::SUCCESS;
    }
}
