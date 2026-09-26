<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Console\Command;

class SupplierStats extends Command
{
    protected $signature = 'supplier:stats';
    protected $description = 'Muestra estadísticas básicas de proveedores y supplier_products';

    public function handle(): int
    {
        $suppliers = Supplier::count();
        $sp = SupplierProduct::count();
        $mapped = SupplierProduct::whereNotNull('product_id')->count();

        $this->info("Suppliers: {$suppliers}");
        $this->info("SupplierProducts: {$sp}");
        $this->info("Mapped to Product: {$mapped}");

        return self::SUCCESS;
    }
}
