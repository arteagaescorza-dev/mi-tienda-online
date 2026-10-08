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

        $task = OperationalTask::where(function ($query) use ($po) {
            $query->where('reference_code', $po->po_number)
                  ->orWhere('action_payload->po_number', $po->po_number)
                  ->orWhere('action_payload->purchase_order_id', $po->id);
        })
        ->where('status', '!=', 'completed')
        ->latest()
        ->first();

        if ($task) {
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
                        'Recepcion Inbound de OC #%s: %d unidades ingresadas a bodega%s.',
                        $po->po_number,
                        $event->totalUnitsReceived,
                        $isFullyReceived ? ' (Orden completada)' : ' (Recepcion parcial)'
                    ),
                    'operator_name' => $operatorName,
                    'metadata' => [
                        'po_id' => $po->id,
                        'po_number' => $po->po_number,
                        'units_received' => $event->totalUnitsReceived,
                        'cost_received' => $event->totalCostReceived,
                        'is_fully_received' => $isFullyReceived,
                        'batch_number' => $batchNumber,
                    ],
                ]);
            }
        }
    }
}

