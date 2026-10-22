<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\CmsCatalogFilter;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        CmsCatalogFilter::firstOrCreate(
            ['filter_key' => 'availability'],
            [
                'label' => 'Disponibilidad (Stock / Bajo Pedido)',
                'display_type' => 'checkbox',
                'sort_order' => 15,
                'is_visible' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        CmsCatalogFilter::where('filter_key', 'availability')->delete();
    }
};

