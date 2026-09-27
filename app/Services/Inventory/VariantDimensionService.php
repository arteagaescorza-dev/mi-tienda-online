<?php

namespace App\Services\Inventory;

use App\Models\ApparelSizeCurve;
use App\Models\ApparelSize;
use App\Models\FragranceVolume;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VariantDimensionService
{
    /**
     * Asegura la existencia de plantillas predeterminadas de curvas, tallas, volumenes y categorias.
     */
    public function ensureBootstrapDefaults(): void
    {
        $this->ensureDefaultCurvesAndSizes();
        $this->ensureDefaultVolumes();
        $this->ensureDefaultCategories();
    }

    public function ensureDefaultCurvesAndSizes(): void
    {
        if (ApparelSizeCurve::withTrashed()->count() > 0) {
            return;
        }

        DB::transaction(function () {
            // 1. Curva Americana Estandar
            $americana = ApparelSizeCurve::create([
                'name' => 'Curva Americana Estandar',
                'slug' => 'curva-americana-estandar',
                'category_scope' => 'textil',
                'description' => 'Tallas comerciales de ropa interior, trajes de bano y playeras',
                'is_default' => true,
                'is_active' => true,
            ]);

            $americanSizes = [
                ['code' => 'XS', 'label' => 'Extra Chica / XS', 'sort_order' => 1, 'waist_min' => 65.0, 'waist_max' => 70.0],
                ['code' => 'CH', 'label' => 'Chica / S', 'sort_order' => 2, 'waist_min' => 71.0, 'waist_max' => 76.0],
                ['code' => 'M', 'label' => 'Mediana / M', 'sort_order' => 3, 'waist_min' => 77.0, 'waist_max' => 84.0],
                ['code' => 'G', 'label' => 'Grande / L', 'sort_order' => 4, 'waist_min' => 85.0, 'waist_max' => 92.0],
                ['code' => 'XL', 'label' => 'Extra Grande / XL', 'sort_order' => 5, 'waist_min' => 93.0, 'waist_max' => 100.0],
                ['code' => '2XL', 'label' => 'Doble Extra Grande / 2XL', 'sort_order' => 6, 'waist_min' => 101.0, 'waist_max' => 110.0],
                ['code' => '3XL', 'label' => 'Triple Extra Grande / 3XL', 'sort_order' => 7, 'waist_min' => 111.0, 'waist_max' => 120.0],
                ['code' => '4XL', 'label' => 'Cuadruple Extra Grande / 4XL', 'sort_order' => 8, 'waist_min' => 121.0, 'waist_max' => 130.0],
            ];

            foreach ($americanSizes as $s) {
                ApparelSize::create([
                    'size_curve_id' => $americana->id,
                    'code' => $s['code'],
                    'label' => $s['label'],
                    'sort_order' => $s['sort_order'],
                    'waist_min_cm' => $s['waist_min'],
                    'waist_max_cm' => $s['waist_max'],
                    'is_active' => true,
                ]);
            }

            // 2. Curva Numerica de Cintura / Pantalon
            $numerica = ApparelSizeCurve::create([
                'name' => 'Curva Numerica Pantalon',
                'slug' => 'curva-numerica-pantalon',
                'category_scope' => 'pantalones',
                'description' => 'Medidas formales en pulgadas mexicanas/americanas para pantalones y bermudas',
                'is_default' => false,
                'is_active' => true,
            ]);

            $numericSizes = [
                ['code' => '28', 'label' => 'Talla 28 (71-74 cm)', 'sort_order' => 1, 'waist_min' => 71.0, 'waist_max' => 74.0],
                ['code' => '30', 'label' => 'Talla 30 (75-79 cm)', 'sort_order' => 2, 'waist_min' => 75.0, 'waist_max' => 79.0],
                ['code' => '32', 'label' => 'Talla 32 (80-84 cm)', 'sort_order' => 3, 'waist_min' => 80.0, 'waist_max' => 84.0],
                ['code' => '34', 'label' => 'Talla 34 (85-89 cm)', 'sort_order' => 4, 'waist_min' => 85.0, 'waist_max' => 89.0],
                ['code' => '36', 'label' => 'Talla 36 (90-95 cm)', 'sort_order' => 5, 'waist_min' => 90.0, 'waist_max' => 95.0],
                ['code' => '38', 'label' => 'Talla 38 (96-102 cm)', 'sort_order' => 6, 'waist_min' => 96.0, 'waist_max' => 102.0],
                ['code' => '40', 'label' => 'Talla 40 (103-108 cm)', 'sort_order' => 7, 'waist_min' => 103.0, 'waist_max' => 108.0],
            ];

            foreach ($numericSizes as $s) {
                ApparelSize::create([
                    'size_curve_id' => $numerica->id,
                    'code' => $s['code'],
                    'label' => $s['label'],
                    'sort_order' => $s['sort_order'],
                    'waist_min_cm' => $s['waist_min'],
                    'waist_max_cm' => $s['waist_max'],
                    'is_active' => true,
                ]);
            }

            // 3. Curva Lenceria y Copas
            $lenceria = ApparelSizeCurve::create([
                'name' => 'Curva Lenceria y Copas',
                'slug' => 'curva-lenceria-copas',
                'category_scope' => 'lenceria',
                'description' => 'Medidas de brasieres, corseteria, copas y bodysuits',
                'is_default' => false,
                'is_active' => true,
            ]);

            $braSizes = [
                ['code' => '32B', 'label' => '32B Copa B', 'sort_order' => 1],
                ['code' => '34B', 'label' => '34B Copa B', 'sort_order' => 2],
                ['code' => '34C', 'label' => '34C Copa C', 'sort_order' => 3],
                ['code' => '36B', 'label' => '36B Copa B', 'sort_order' => 4],
                ['code' => '36C', 'label' => '36C Copa C', 'sort_order' => 5],
                ['code' => '38D', 'label' => '38D Copa D', 'sort_order' => 6],
            ];

            foreach ($braSizes as $s) {
                ApparelSize::create([
                    'size_curve_id' => $lenceria->id,
                    'code' => $s['code'],
                    'label' => $s['label'],
                    'sort_order' => $s['sort_order'],
                    'is_active' => true,
                ]);
            }

            // 4. Unitalla Universal
            $unitalla = ApparelSizeCurve::create([
                'name' => 'Unitalla Universal',
                'slug' => 'curva-unitalla-universal',
                'category_scope' => 'universal',
                'description' => 'Prendas con elastico o tiras auto-ajustables que no requieren curva',
                'is_default' => false,
                'is_active' => true,
            ]);

            ApparelSize::create([
                'size_curve_id' => $unitalla->id,
                'code' => 'UNITALLA',
                'label' => 'Unitalla Ajustable',
                'sort_order' => 1,
                'is_active' => true,
            ]);
        });
    }

    public function ensureDefaultVolumes(): void
    {
        if (FragranceVolume::withTrashed()->count() > 0) {
            return;
        }

        DB::transaction(function () {
            $defaultVolumes = [
                ['value' => 10.00, 'unit' => 'ml', 'label' => '10ml Travel Decant', 'description' => 'Formato portatil de bolsillo para pruebas', 'sort_order' => 1],
                ['value' => 15.00, 'unit' => 'ml', 'label' => '15ml Pocket Spray', 'description' => 'Vial atomizador para viaje', 'sort_order' => 2],
                ['value' => 30.00, 'unit' => 'ml', 'label' => '30ml Eau de Toilette', 'description' => 'Presentacion compacta uso regular', 'sort_order' => 3],
                ['value' => 50.00, 'unit' => 'ml', 'label' => '50ml Eau de Parfum', 'description' => 'Tamano mediano alta concentracion', 'sort_order' => 4],
                ['value' => 75.00, 'unit' => 'ml', 'label' => '75ml Classic Spray', 'description' => 'Presentacion clasica de tocador', 'sort_order' => 5],
                ['value' => 100.00, 'unit' => 'ml', 'label' => '100ml / 3.4 oz Full Size', 'description' => 'Tamano regular estandar comercial', 'sort_order' => 6],
                ['value' => 120.00, 'unit' => 'ml', 'label' => '120ml Barber Lotion', 'description' => 'Locion para afeitado o refrescante', 'sort_order' => 7],
                ['value' => 200.00, 'unit' => 'ml', 'label' => '200ml Jumbo Size', 'description' => 'Envase jumbo economico o recarga', 'sort_order' => 8],
            ];

            foreach ($defaultVolumes as $v) {
                FragranceVolume::create([
                    'value' => $v['value'],
                    'unit' => $v['unit'],
                    'label' => $v['label'],
                    'description' => $v['description'],
                    'sort_order' => $v['sort_order'],
                    'is_active' => true,
                ]);
            }
        });
    }

    public function ensureDefaultCategories(): void
    {
        if (Category::withTrashed()->count() > 0) {
            return;
        }

        DB::transaction(function () {
            $tree = [
                [
                    'name' => 'Ropa Interior Masculina',
                    'slug' => 'ropa-interior-masculina',
                    'description' => 'Boxers, jockstraps, suspensorios y briefs anatomicos con soporte',
                    'children' => [
                        ['name' => 'Boxers & Trunks', 'slug' => 'boxers-trunks', 'description' => 'Corte trunk y boxer ajustado en microfibra y algodon'],
                        ['name' => 'Jockstraps Deportivos', 'slug' => 'jockstraps-deportivos', 'description' => 'Suspensorios con elásticos reforzados para soporte y gimnasio'],
                        ['name' => 'Briefs Clasicos', 'slug' => 'briefs-clasicos', 'description' => 'Corte clasico de pierna descubierta'],
                        ['name' => 'Tangas & Thongs', 'slug' => 'tangas-thongs', 'description' => 'Siluetas minimas con corte estilizado'],
                    ],
                ],
                [
                    'name' => 'Lenceria & Sensual',
                    'slug' => 'lenceria-sensual',
                    'description' => 'Prendas intimas de noche, fetiche suave, encajes y transparencias',
                    'children' => [
                        ['name' => 'Arneses & Pecheras', 'slug' => 'arneses-pecheras', 'description' => 'Arneses elasticos ajustables de pecho y hombros'],
                        ['name' => 'Bodysuits & Singlets', 'slug' => 'bodysuits-singlets', 'description' => 'Prendas completas de cuerpo de rejilla y spandex'],
                        ['name' => 'Lenceria Erotica', 'slug' => 'lenceria-erotica', 'description' => 'Disenos con aperturas y tejidos transparentes'],
                    ],
                ],
                [
                    'name' => 'Cosmetica & Cuidado Intimo',
                    'slug' => 'cosmetica-cuidado-intimo',
                    'description' => 'Fragancias de nicho, feromonas, lubricantes y aceites de masaje',
                    'children' => [
                        ['name' => 'Fragancias & Perfumes', 'slug' => 'fragancias-perfumes', 'description' => 'Perfumeria de autor y decants exclusivos'],
                        ['name' => 'Lubricantes Especializados', 'slug' => 'lubricantes-especializados', 'description' => 'Formulas base agua, silicon y desensibilizantes'],
                        ['name' => 'Skincare & Tratamiento', 'slug' => 'skincare-tratamiento', 'description' => 'Cremas hidratantes, aceites corporales y jabones intimos'],
                    ],
                ],
                [
                    'name' => 'Accesorios & Ropa de Fiesta',
                    'slug' => 'accesorios-ropa-fiesta',
                    'description' => 'Tops, playeras festivaleras, calcetas y accesorios de circuito',
                    'children' => [
                        ['name' => 'Playeras & Tank Tops', 'slug' => 'playeras-tank-tops', 'description' => 'Camisetas sin mangas transpirables para fiesta'],
                        ['name' => 'Calcetas & Accesorios', 'slug' => 'calcetas-accesorios', 'description' => 'Calcetas deportivas de tubo y complementos'],
                    ],
                ],
            ];

            foreach ($tree as $parentData) {
                $children = $parentData['children'] ?? [];
                unset($parentData['children']);
                $parentData['is_active'] = true;

                $parent = Category::create($parentData);

                foreach ($children as $childData) {
                    $childData['parent_id'] = $parent->id;
                    $childData['is_active'] = true;
                    Category::create($childData);
                }
            }
        });
    }

    // =========================================================================
    // CRUD: CURVAS DE TALLAS
    // =========================================================================

    public function getAllCurves(bool $includeArchived = false)
    {
        $query = ApparelSizeCurve::query()->with(['sizes' => function ($q) use ($includeArchived) {
            if ($includeArchived) {
                $q->withTrashed();
            }
            $q->orderBy('sort_order');
        }]);

        if ($includeArchived) {
            $query->withTrashed();
        }

        return $query->orderBy('is_default', 'desc')->orderBy('name')->get();
    }

    public function createCurve(array $data): ApparelSizeCurve
    {
        return DB::transaction(function () use ($data) {
            $slug = Str::slug($data['name']);
            $count = ApparelSizeCurve::withTrashed()->where('slug', 'like', "{$slug}%")->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            if (!empty($data['is_default'])) {
                ApparelSizeCurve::query()->update(['is_default' => false]);
            }

            $curve = ApparelSizeCurve::create([
                'name' => $data['name'],
                'slug' => $slug,
                'category_scope' => $data['category_scope'] ?? 'textil',
                'description' => $data['description'] ?? null,
                'is_default' => !empty($data['is_default']),
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (!empty($data['sizes']) && is_array($data['sizes'])) {
                foreach ($data['sizes'] as $index => $sizeItem) {
                    $this->addSizeToCurve($curve->id, [
                        'code' => $sizeItem['code'],
                        'label' => $sizeItem['label'] ?? $sizeItem['code'],
                        'sort_order' => $sizeItem['sort_order'] ?? ($index + 1),
                        'waist_min_cm' => $sizeItem['waist_min_cm'] ?? null,
                        'waist_max_cm' => $sizeItem['waist_max_cm'] ?? null,
                        'is_active' => $sizeItem['is_active'] ?? true,
                    ]);
                }
            }

            return $curve->load('sizes');
        });
    }

    public function updateCurve(int $id, array $data): ApparelSizeCurve
    {
        $curve = ApparelSizeCurve::withTrashed()->findOrFail($id);

        if (!empty($data['is_default']) && !$curve->is_default) {
            ApparelSizeCurve::where('id', '!=', $curve->id)->update(['is_default' => false]);
        }

        $fillable = array_intersect_key($data, array_flip(['name', 'category_scope', 'description', 'is_default', 'is_active']));
        $curve->update($fillable);

        return $curve->load('sizes');
    }

    public function toggleActiveCurve(int $id): ApparelSizeCurve
    {
        $curve = ApparelSizeCurve::withTrashed()->findOrFail($id);
        $curve->is_active = !$curve->is_active;
        $curve->save();
        return $curve->load('sizes');
    }

    public function archiveCurve(int $id): bool
    {
        $curve = ApparelSizeCurve::findOrFail($id);
        return (bool) $curve->delete();
    }

    public function restoreCurve(int $id): ApparelSizeCurve
    {
        $curve = ApparelSizeCurve::withTrashed()->findOrFail($id);
        $curve->restore();
        return $curve->load('sizes');
    }

    public function deleteCurve(int $id): bool
    {
        $curve = ApparelSizeCurve::withTrashed()->findOrFail($id);
        return (bool) $curve->forceDelete();
    }

    // =========================================================================
    // CRUD: TALLAS INDIVIDUALES
    // =========================================================================

    public function addSizeToCurve(int $curveId, array $data): ApparelSize
    {
        $curve = ApparelSizeCurve::withTrashed()->findOrFail($curveId);

        $maxSort = $curve->sizes()->max('sort_order') ?? 0;

        return ApparelSize::create([
            'size_curve_id' => $curve->id,
            'code' => trim($data['code']),
            'label' => trim($data['label'] ?? $data['code']),
            'sort_order' => $data['sort_order'] ?? ($maxSort + 1),
            'waist_min_cm' => $data['waist_min_cm'] ?? null,
            'waist_max_cm' => $data['waist_max_cm'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateSize(int $sizeId, array $data): ApparelSize
    {
        $size = ApparelSize::withTrashed()->findOrFail($sizeId);
        $fillable = array_intersect_key($data, array_flip(['code', 'label', 'sort_order', 'waist_min_cm', 'waist_max_cm', 'is_active']));
        $size->update($fillable);
        return $size;
    }

    public function toggleActiveSize(int $sizeId): ApparelSize
    {
        $size = ApparelSize::withTrashed()->findOrFail($sizeId);
        $size->is_active = !$size->is_active;
        $size->save();
        return $size;
    }

    public function archiveSize(int $sizeId): bool
    {
        $size = ApparelSize::findOrFail($sizeId);
        return (bool) $size->delete();
    }

    public function restoreSize(int $sizeId): ApparelSize
    {
        $size = ApparelSize::withTrashed()->findOrFail($sizeId);
        $size->restore();
        return $size;
    }

    public function deleteSize(int $sizeId): bool
    {
        $size = ApparelSize::withTrashed()->findOrFail($sizeId);
        return (bool) $size->forceDelete();
    }

    // =========================================================================
    // CRUD: VOLUMENES DE PERFUMERIA
    // =========================================================================

    public function getAllVolumes(bool $includeArchived = false)
    {
        $query = FragranceVolume::query();
        if ($includeArchived) {
            $query->withTrashed();
        }
        return $query->orderBy('sort_order')->orderBy('value')->get();
    }

    public function createVolume(array $data): FragranceVolume
    {
        $maxSort = FragranceVolume::withTrashed()->max('sort_order') ?? 0;

        return FragranceVolume::create([
            'value' => $data['value'],
            'unit' => $data['unit'] ?? 'ml',
            'label' => $data['label'] ?? ($data['value'] . ($data['unit'] ?? 'ml')),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? ($maxSort + 1),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateVolume(int $id, array $data): FragranceVolume
    {
        $volume = FragranceVolume::withTrashed()->findOrFail($id);
        $fillable = array_intersect_key($data, array_flip(['value', 'unit', 'label', 'description', 'sort_order', 'is_active']));
        $volume->update($fillable);
        return $volume;
    }

    public function toggleActiveVolume(int $id): FragranceVolume
    {
        $volume = FragranceVolume::withTrashed()->findOrFail($id);
        $volume->is_active = !$volume->is_active;
        $volume->save();
        return $volume;
    }

    public function archiveVolume(int $id): bool
    {
        $volume = FragranceVolume::findOrFail($id);
        return (bool) $volume->delete();
    }

    public function restoreVolume(int $id): FragranceVolume
    {
        $volume = FragranceVolume::withTrashed()->findOrFail($id);
        $volume->restore();
        return $volume;
    }

    public function deleteVolume(int $id): bool
    {
        $volume = FragranceVolume::withTrashed()->findOrFail($id);
        return (bool) $volume->forceDelete();
    }

    // =========================================================================
    // CRUD: CATEGORIAS Y SUBCATEGORIAS
    // =========================================================================

    public function getAllCategories(bool $includeArchived = false)
    {
        $query = Category::query()->with(['children' => function ($q) use ($includeArchived) {
            if ($includeArchived) {
                $q->withTrashed();
            }
            $q->orderBy('name');
        }]);

        if ($includeArchived) {
            $query->withTrashed();
        }

        return $query->roots()->orderBy('name')->get();
    }

    public function createCategory(array $data): Category
    {
        $slug = Str::slug($data['name']);
        $count = Category::withTrashed()->where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        return Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'is_active' => $data['is_active'] ?? true,
            'meta' => $data['meta'] ?? null,
        ]);
    }

    public function updateCategory(int $id, array $data): Category
    {
        $category = Category::withTrashed()->findOrFail($id);

        if (!empty($data['name']) && $data['name'] !== $category->name && empty($data['slug'])) {
            $slug = Str::slug($data['name']);
            $count = Category::withTrashed()->where('slug', 'like', "{$slug}%")->where('id', '!=', $category->id)->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }
            $data['slug'] = $slug;
        }

        $fillable = array_intersect_key($data, array_flip(['name', 'slug', 'description', 'parent_id', 'is_active', 'meta']));
        if (array_key_exists('parent_id', $fillable) && empty($fillable['parent_id'])) {
            $fillable['parent_id'] = null;
        }

        $category->update($fillable);
        return $category->load('children');
    }

    public function toggleActiveCategory(int $id): Category
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        return $category->load('children');
    }

    public function archiveCategory(int $id): bool
    {
        $category = Category::findOrFail($id);
        return (bool) $category->delete();
    }

    public function restoreCategory(int $id): Category
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();
        return $category->load('children');
    }

    public function deleteCategory(int $id): bool
    {
        $category = Category::withTrashed()->findOrFail($id);

        // Desvincular productos si los hubiera o verificar hijos
        if ($category->children()->count() > 0) {
            $category->children()->update(['parent_id' => null]);
        }

        return (bool) $category->forceDelete();
    }
}
