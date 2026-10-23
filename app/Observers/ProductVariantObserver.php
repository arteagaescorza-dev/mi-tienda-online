<?php

namespace App\Observers;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;

class ProductVariantObserver
{
    /**
     * Limpia la caché de vitrinas de Hub 2 ante cualquier alteración de variantes (PVP, SKU, tallas).
     */
    protected function invalidateStorefrontCache(ProductVariant $variant): void
    {
        Cache::forget('cms_storefront_marte');
        Cache::forget('cms_storefront_venus');
        Cache::forget('cms_storefront_tiresias');
        Cache::forget('cms_storefront_terra');
    }

    public function created(ProductVariant $variant): void
    {
        $this->invalidateStorefrontCache($variant);
    }

    public function updated(ProductVariant $variant): void
    {
        $this->invalidateStorefrontCache($variant);
    }

    public function deleted(ProductVariant $variant): void
    {
        $this->invalidateStorefrontCache($variant);
    }

    public function restored(ProductVariant $variant): void
    {
        $this->invalidateStorefrontCache($variant);
    }
}

