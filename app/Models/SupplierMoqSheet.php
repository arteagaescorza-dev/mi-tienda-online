<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierMoqSheet extends Model
{
    use HasFactory;

    protected $table = 'supplier_moq_sheets';

    protected $fillable = [
        'supplier_id',
        'supplier_name',
        'title',
        'validity',
        'currency',
        'tiers',
        'rows',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'tiers' => 'array',
        'rows' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'moq_sheet_id');
    }

    /**
     * Determina el tier de MOQ aplicable según la cantidad total de prendas.
     */
    public function resolveTier(int $totalQty): array
    {
        $tiers = $this->tiers ?? [];
        if (empty($tiers)) {
            return [
                'key' => 'menudeo',
                'label' => 'Menudeo',
                'min_qty' => 1,
            ];
        }

        // Ordenar descendentemente por min_qty
        usort($tiers, fn ($a, $b) => ($b['min_qty'] ?? 0) <=> ($a['min_qty'] ?? 0));

        foreach ($tiers as $tier) {
            $min = (int) ($tier['min_qty'] ?? 1);
            if ($totalQty >= $min) {
                return $tier;
            }
        }

        return end($tiers) ?: ['key' => 'menudeo', 'label' => 'Menudeo', 'min_qty' => 1];
    }

    /**
     * Resuelve el costo unitario para una categoría o prenda dada y un tier o cantidad.
     */
    public function getUnitPrice(string $categoryOrProduct, int|string $tierKeyOrQty): ?float
    {
        $rows = $this->rows ?? [];
        $tierKey = is_numeric($tierKeyOrQty) ? ($this->resolveTier((int) $tierKeyOrQty)['key'] ?? 'menudeo') : $tierKeyOrQty;

        $targetCategory = mb_strtolower(trim($categoryOrProduct));

        foreach ($rows as $row) {
            $rowCat = mb_strtolower(trim($row['category'] ?? ''));
            if ($rowCat === $targetCategory || str_contains($targetCategory, $rowCat) || str_contains($rowCat, $targetCategory)) {
                $prices = $row['prices'] ?? [];
                if (isset($prices[$tierKey])) {
                    return (float) $prices[$tierKey];
                }
                // Si no existe esa escala exacta, buscar la primera disponible
                return isset($prices['menudeo']) ? (float) $prices['menudeo'] : null;
            }
        }

        return null;
    }

    /**
     * Precarga los datos por defecto oficiales de Torzo 2026 si la tabla está vacía.
     */
    public static function ensureTorzoDefault(): self
    {
        $existing = self::where('supplier_name', 'like', '%Torzo%')->first();
        if ($existing) {
            return $existing;
        }

        $supplier = Supplier::where('name', 'like', '%Torzo%')->first();

        return self::create([
            'supplier_id' => $supplier?->id,
            'supplier_name' => 'Torzo B2B',
            'title' => 'Torzo Precios Mayoreo 2026',
            'validity' => 'Vigencia: Junio 2026',
            'currency' => 'MXN',
            'is_active' => true,
            'is_default' => true,
            'notes' => 'Tabulador oficial de Torzo escalonado por volumen de prendas (Menudeo hasta 400+ prendas).',
            'tiers' => [
                ['key' => 'menudeo', 'label' => 'Menudeo', 'min_qty' => 1, 'color' => '#10b981', 'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                ['key' => 'moq_50', 'label' => '50 prendas', 'min_qty' => 50, 'color' => '#ef4444', 'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                ['key' => 'moq_100', 'label' => '100 prendas', 'min_qty' => 100, 'color' => '#f59e0b', 'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                ['key' => 'moq_200', 'label' => '200 prendas', 'min_qty' => 200, 'color' => '#38bdf8', 'badge_class' => 'bg-sky-50 text-sky-700 border-sky-200'],
                ['key' => 'moq_300', 'label' => '300 prendas', 'min_qty' => 300, 'color' => '#a855f7', 'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                ['key' => 'moq_400', 'label' => '400 prendas', 'min_qty' => 400, 'color' => '#ec4899', 'badge_class' => 'bg-pink-50 text-pink-700 border-pink-200'],
            ],
            'rows' => [
                [
                    'category' => 'Tanga',
                    'icon' => 'tanga',
                    'prices' => ['menudeo' => 180, 'moq_50' => 90, 'moq_100' => 85, 'moq_200' => 80, 'moq_300' => 75, 'moq_400' => 70],
                ],
                [
                    'category' => 'Bikinis',
                    'icon' => 'bikinis',
                    'prices' => ['menudeo' => 220, 'moq_50' => 110, 'moq_100' => 105, 'moq_200' => 100, 'moq_300' => 95, 'moq_400' => 90],
                ],
                [
                    'category' => 'Jocks',
                    'icon' => 'jocks',
                    'prices' => ['menudeo' => 250, 'moq_50' => 130, 'moq_100' => 125, 'moq_200' => 120, 'moq_300' => 115, 'moq_400' => 110],
                ],
                [
                    'category' => 'Boxers',
                    'icon' => 'boxers',
                    'prices' => ['menudeo' => 280, 'moq_50' => 140, 'moq_100' => 135, 'moq_200' => 130, 'moq_300' => 125, 'moq_400' => 120],
                ],
                [
                    'category' => 'Tanktop',
                    'icon' => 'tanktop',
                    'prices' => ['menudeo' => 320, 'moq_50' => 160, 'moq_100' => 155, 'moq_200' => 150, 'moq_300' => 145, 'moq_400' => 140],
                ],
                [
                    'category' => 'T-Shirt',
                    'icon' => 'tshirt',
                    'prices' => ['menudeo' => 380, 'moq_50' => 190, 'moq_100' => 185, 'moq_200' => 180, 'moq_300' => 175, 'moq_400' => 170],
                ],
                [
                    'category' => 'Swimwear',
                    'icon' => 'swimwear',
                    'prices' => ['menudeo' => 460, 'moq_50' => 230, 'moq_100' => 225, 'moq_200' => 220, 'moq_300' => 215, 'moq_400' => 210],
                ],
                [
                    'category' => 'Combo (camisa + short)',
                    'icon' => 'combo',
                    'prices' => ['menudeo' => 1180, 'moq_50' => 590, 'moq_100' => 585, 'moq_200' => 580, 'moq_300' => 575, 'moq_400' => 570],
                ],
                [
                    'category' => 'Abanico',
                    'icon' => 'abanico',
                    'prices' => ['menudeo' => 400, 'moq_50' => 210, 'moq_100' => 200, 'moq_200' => 190, 'moq_300' => 185, 'moq_400' => 180],
                ],
            ],
        ]);
    }
}
