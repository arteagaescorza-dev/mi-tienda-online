<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Separa estrictamente las Líneas de Producto / Tipos de Prenda de las Marcas Comerciales:
     * 1. Elimina de la tabla `brands` cualquier registro espurio que coincida con tipos de prenda/categorías.
     * 2. Actualiza los productos cuyo campo `brand` contenga una categoría (ej. TANGA, BIKINI, JOCKSTRAPS, etc.)
     *    asociándolos a su marca comercial real (ej. 'Torzo') y preservando la tipología en la categoría.
     * 3. Sanea la columna `brands` de la tabla `suppliers`.
     */
    public function up(): void
    {
        $categoryNames = [
            'TANGA', 'Tanga', 'tanga', 'TANGAS', 'tangas',
            'BIKINI', 'Bikini', 'bikini', 'BIKINIS', 'bikinis',
            'JOCKSTRAPS', 'Jockstraps', 'jockstraps', 'JOCKSTRAP', 'jockstrap', 'JOCKS', 'jocks',
            'BRIEF/BOXERS', 'Brief/Boxers', 'brief/boxers', 'BRIEFS', 'briefs', 'BOXERS', 'boxers', 'BOXER', 'boxer',
            'TANK TOPS', 'Tank Tops', 'tank tops', 'TANK TOP', 'tank top',
            'T-SHIRTS', 'T-Shirts', 't-shirts', 'T-SHIRT', 't-shirt',
            'SWIMWEAR', 'Swimwear', 'swimwear',
            'COMBOS', 'Combos', 'combos', 'COMBO', 'combo',
            'ACCESORIOS', 'Accesorios', 'accesorios',
            'SKINCARE', 'Skincare', 'skincare',
            'PERFUMERIA', 'Perfumeria', 'perfumeria', 'PERFUMERÍA', 'perfumería',
        ];

        // 1. Eliminar categorías erróneas de la tabla brands si existen
        DB::table('brands')
            ->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', Supplier::PRODUCT_LINE_CATEGORIES))
            ->delete();

        // 2. Normalizar productos que tienen categoría como marca hacia 'Torzo'
        // Detectar ID de proveedor Torzo
        $torzo = DB::table('suppliers')->where('name', 'like', '%Torzo%')->first();
        $torzoSupplierId = $torzo?->id;

        // Actualizar productos de Torzo o con marcas que coinciden con categorías
        DB::table('products')
            ->whereIn('brand', $categoryNames)
            ->update([
                'brand' => 'Torzo',
                'updated_at' => now(),
            ]);

        // 3. Si el proveedor Torzo tiene las categorías guardadas en su columna JSON/array brands, limpiarlo
        if ($torzo) {
            $brands = json_decode($torzo->brands ?? '[]', true);
            if (is_array($brands) && !empty($brands)) {
                $cleanedBrands = array_values(array_filter($brands, function ($b) {
                    return !Supplier::isProductLineCategory($b);
                }));
                if (empty($cleanedBrands)) {
                    $cleanedBrands = ['Torzo'];
                }
                DB::table('suppliers')->where('id', $torzo->id)->update([
                    'brands' => json_encode($cleanedBrands),
                ]);
            }

            // Asegurar que exista la marca 'Torzo' en la tabla brands vinculada a este proveedor
            $torzoBrand = DB::table('brands')->where('name', 'Torzo')->first();
            if (!$torzoBrand) {
                DB::table('brands')->insert([
                    'supplier_id' => $torzo->id,
                    'name' => 'Torzo',
                    'slug' => 'torzo',
                    'description' => 'Marca comercial oficial Torzo',
                    'is_active' => 1,
                    'is_archived' => 0,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('brands')->where('id', $torzoBrand->id)->update([
                    'supplier_id' => $torzo->id,
                    'is_active' => 1,
                    'is_archived' => 0,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es reversible la re-introducción de categorías erróneas en marcas comerciales
    }
};
