<?php

use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sincroniza y asocia obligatoriamente todas las marcas a un proveedor (Regla 1).
     */
    public function up(): void
    {
        $defaultSupplier = Supplier::first();
        $torzo = Supplier::where('name', 'like', '%Torzo%')->first() ?: $defaultSupplier;

        if ($torzo) {
            // Asociar marcas huerfanas conocidas a Torzo o proveedor por defecto
            Brand::where('name', 'like', '%Torzo%')->update(['supplier_id' => $torzo->id]);
            Brand::where('name', 'Mazho')->update(['supplier_id' => $torzo->id]);
        }

        // Si quedase alguna otra marca con supplier_id null, asociarla al primer proveedor valido
        if ($defaultSupplier) {
            Brand::whereNull('supplier_id')->update(['supplier_id' => $defaultSupplier->id]);
        }

        // Sincronizar array brands en cada proveedor
        $suppliers = Supplier::all();
        foreach ($suppliers as $sup) {
            $brandNames = Brand::where('supplier_id', $sup->id)
                ->pluck('name')
                ->filter(fn ($b) => !empty($b) && !Supplier::isProductLineCategory($b))
                ->toArray();

            $existing = is_array($sup->brands) ? $sup->brands : [];
            $merged = array_values(array_unique(array_filter(array_merge($existing, $brandNames), fn($b) => !Supplier::isProductLineCategory($b))));
            
            if (empty($merged) && !Supplier::isProductLineCategory($sup->name)) {
                $merged = [$sup->name];
            }

            $sup->brands = $merged;
            $sup->save();
        }
    }

    public function down(): void
    {
        // No destructivo
    }
};

