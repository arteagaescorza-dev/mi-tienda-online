<?php

namespace App\Observers;

use App\Models\Inventory;
use Illuminate\Support\Facades\Cache;

class InventoryObserver
{
    /**
     * Limpia la caché de vitrinas de Hub 2 ante cualquier cambio en existencias físicas o reservas.
     */
    protected function invalidateStorefrontCache(Inventory $inventory): void
    {
        Cache::forget('cms_storefront_marte');
        Cache::forget('cms_storefront_venus');
        Cache::forget('cms_storefront_tiresias');
        Cache::forget('cms_storefront_terra');
    }

    public function created(Inventory $inventory): void
    {
        $this->invalidateStorefrontCache($inventory);
    }

    public function updated(Inventory $inventory): void
    {
        $this->invalidateStorefrontCache($inventory);
    }

    public function deleted(Inventory $inventory): void
    {
        $this->invalidateStorefrontCache($inventory);
    }

    public function restored(Inventory $inventory): void
    {
        $this->invalidateStorefrontCache($inventory);
    }
}

