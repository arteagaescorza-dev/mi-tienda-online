<?php

namespace App\Listeners\Fulfillment;

use App\Events\Inventory\PurchaseOrderReceived;
use App\Models\WarehouseRampEntry;
use App\Models\OperationalTask;
use Illuminate\Support\Facades\Schema;

class ClearRampEntryOnPoReceived
{
    public function handle(PurchaseOrderReceived $event)
    {
        if (!Schema::hasTable('warehouse_ramp_entries')) {
            return;
        }

        $po = $event->purchaseOrder;
        $rampEntry = null;
        $cleanPo = preg_replace('/[^A-Za-z0-9]/', '', (string) $po->po_number);

        // Try to find through operational task if exists
        if (Schema::hasTable('operational_tasks')) {
            $task = OperationalTask::where(function ($query) use ($po) {
                $query->where('reference_code', $po->po_number)
                      ->orWhere('action_payload->po_number', $po->po_number)
                      ->orWhere('action_payload->purchase_order_id', $po->id);
            })->latest()->first();

            if ($task && !empty($task->action_payload['ramp_entry_id'])) {
                $rampEntry = WarehouseRampEntry::find($task->action_payload['ramp_entry_id']);
            }
        }

        if (!$rampEntry) {
            $rampEntry = WarehouseRampEntry::where('status', 'in_dock')
                ->where(function ($q) use ($po, $cleanPo) {
                    if (!empty($po->tracking_number)) {
                        $q->orWhere('entry_folio', 'LIKE', '%' . $po->tracking_number . '%');
                    }
                    if (!empty($cleanPo)) {
                        $q->orWhere('entry_folio', 'LIKE', '%' . substr($cleanPo, 0, 15) . '%');
                    }
                    if ($po->supplier && !empty($po->supplier->name)) {
                        $q->orWhere('carrier_or_supplier', 'LIKE', '%' . $po->supplier->name . '%');
                    }
                    if (!empty($po->carrier)) {
                        $q->orWhere('carrier_or_supplier', 'LIKE', '%' . $po->carrier . '%');
                    }
                })
                ->latest()
                ->first();
        }

        if ($rampEntry && $rampEntry->status !== 'cleared') {
            $rampEntry->status = 'unloaded';
            $rampEntry->cleared_at = now();
            $rampEntry->packages_received_count = ($rampEntry->packages_received_count ?: 0) + $event->totalUnitsReceived;
            $rampEntry->save();
        }
    }
}

