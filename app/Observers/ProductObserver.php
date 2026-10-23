<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Limpia la caché de las vitrinas omnicanal de Hub 2 tras cualquier cambio de producto.
     */
    protected function invalidateStorefrontCache(Product $product): void
    {
        Cache::forget('cms_storefront_marte');
        Cache::forget('cms_storefront_venus');
        Cache::forget('cms_storefront_tiresias');
        Cache::forget('cms_storefront_terra');
        Cache::forget('cms_announcements');
        Cache::forget('cms_banners');
    }

    public function created(Product $product): void
    {
        $this->invalidateStorefrontCache($product);
    }

    public function updated(Product $product): void
    {
        $this->invalidateStorefrontCache($product);
    }

    public function deleted(Product $product): void
    {
        $this->invalidateStorefrontCache($product);
    }

    public function restored(Product $product): void
    {
        $this->invalidateStorefrontCache($product);
    }
}

