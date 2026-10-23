<?php

namespace App\Listeners\ActionCenter;

use App\Events\Inventory\PurchaseOrderReceived;
use App\Models\OperationalTask;
use App\Models\ShiftLog;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class UpdateOperationalTaskOnPoReceived
{
    public function handle(PurchaseOrderReceived $event)
    {
        if (!Schema::hasTable('operational_tasks')) {
            return;
        }

        $po = $event->purchaseOrder;
        $isFullyReceived = $event->isFullyReceived;
        $batchNumber = $event->sanitaryData['batch_number'] ?? null;
        
        $operatorName = 'Operador Almacen';
        if ($event->operatorId) {
            $operator = User::find($event->operatorId);
            if ($operator) $operatorName = $operator->name;
        }

        $poNumber = (string) $po->po_number;
        $cleanPo = ltrim($poNumber, '#');
        $codes = array_values(array_unique(array_filter([
            $poNumber,
            '#' . $cleanPo,
            $cleanPo,
        ])));

        $tasks = OperationalTask::where(function ($query) use ($codes, $po) {
            $query->whereIn('reference_code', $codes)
                  ->orWhereIn('action_payload->po_number', $codes)
                  ->orWhere('action_payload->purchase_order_id', $po->id);
        })
        ->where('status', '!=', 'completed')
        ->get();

        foreach ($tasks as $task) {
            $task->status = $isFullyReceived ? 'completed' : 'in_progress';
            if ($isFullyReceived) {
                $task->completed_at = now();
                $task->completed_by = $operatorName;
            }
            $task->save();

            if (Schema::hasTable('shift_logs')) {
                ShiftLog::create([
                    'operational_task_id' => $task->id,
                    'event_type' => 'inbound_received',
                    'message' => sprintf(
                        'Recepcion Inbound de OC #%s: %d unidades ingresadas a bodega%s. (Asignado: %s)',
                        $cleanPo,
                        $event->totalUnitsReceived,
                        $isFullyReceived ? ' (Orden completada)' : ' (Recepcion parcial)',
                        $task->assigned_role ?? 'general'
                    ),
                    'operator_name' => $operatorName,
                    'metadata' => [
                        'po_id' => $po->id,
                        'po_number' => $poNumber,
                        'units_received' => $event->totalUnitsReceived,
                        'cost_received' => $event->totalCostReceived,
                        'is_fully_received' => $isFullyReceived,
                        'batch_number' => $batchNumber,
                        'assigned_role' => $task->assigned_role,
                    ],
                ]);
            }
        }
    }
}

