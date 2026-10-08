<?php

namespace App\Events\Inventory;

use App\Models\PurchaseOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $purchaseOrder;
    public $itemsReceived;
    public $totalUnitsReceived;
    public $totalCostReceived;
    public $operatorId;
    public $sanitaryData;
    public $isFullyReceived;

    public function __construct(
        PurchaseOrder $purchaseOrder,
        array $itemsReceived,
        int $totalUnitsReceived,
        float $totalCostReceived,
        ?int $operatorId,
        array $sanitaryData,
        bool $isFullyReceived
    ) {
        $this->purchaseOrder = $purchaseOrder;
        $this->itemsReceived = $itemsReceived;
        $this->totalUnitsReceived = $totalUnitsReceived;
        $this->totalCostReceived = $totalCostReceived;
        $this->operatorId = $operatorId;
        $this->sanitaryData = $sanitaryData;
        $this->isFullyReceived = $isFullyReceived;
    }
}

