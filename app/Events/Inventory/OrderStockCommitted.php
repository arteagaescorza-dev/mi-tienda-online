<?php

namespace App\Events\Inventory;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStockCommitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cartId;
    public $orderId;
    public $userId;

    public function __construct(string $cartId, int $orderId, ?int $userId = null)
    {
        $this->cartId = $cartId;
        $this->orderId = $orderId;
        $this->userId = $userId;
    }
}

