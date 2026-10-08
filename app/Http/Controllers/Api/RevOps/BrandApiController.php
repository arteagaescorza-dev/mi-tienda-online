<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Api\RevOps\StoreBrandRequest;
use App\Http\Requests\Api\RevOps\UpdateBrandRequest;

class BrandApiController extends Controller
{
    public function __construct()
    {
        // Autorización estricta por diseño para todas las operaciones (SSOT Ciberseguridad)
        // Solo usuarios con permisos de gestión de catálogo pueden acceder a este controlador.
    }

    /**
     * Listado maestro de marcas comerciales.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalog');

        $status = $request->query('status', 'all');
        $supplierId = $request->query('supplier_id');
        $search = $request->query('search');

        $query = Brand::with(['supplier:id,name,legal_name,vendor_code,origin,status']);

        if ($status === 'active') {
            $query->where('is_active', true)->where('is_archived', false);
        } elseif ($status === 'archived') {
            $query->where('is_archived', true);
        }

        if (!empty($supplierId)) {
            if ($supplierId === 'unassigned') {
                $query->whereNull('supplier_id');
            } else {
                $query->where('supplier_id', $supplierId);
            }
        }

        if (!empty($search)) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('supplier', function ($sq) use ($term) {
                        $sq->where('name', 'like', "%{$term}%")
                            ->orWhere('legal_name', 'like', "%{$term}%");
                    });
            });
        }

        $brands = $query->orderBy('sort_order')->orderBy('name')->get();

        if ($brands->isEmpty() && empty($search) && empty($supplierId)) {
            $this->seedInitialBrands();
            $brands = Brand::with(['supplier:id,name,legal_name,vendor_code,origin,status'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        $mapped = $brands->map(function ($brand) {
            $skuCount = Product::where('brand', $brand->name)->count();
            return [
                'id' => $brand->id,
                'supplier_id' => $brand->supplier_id,
                'supplier_name' => $brand->supplier ? ($brand->supplier->trade_name ?? $brand->supplier->name) : 'Marca Directa / Independiente',
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo_url' => $brand->logo_url,
                'website' => $brand->website,
                'is_active' => (bool)$brand->is_active,
                'is_archived' => (bool)$brand->is_archived,
                'sort_order' => $brand->sort_order,
                'sku_count' => $skuCount,
                'created_at' => $brand->created_at?->toIso8601String(),
                'updated_at' => $brand->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $mapped,
            'count' => $mapped->count(),
        ]);
    }

    /**
     * Alta manual de una nueva marca comercial.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $rawName = $validated['name'];
        if (Supplier::isProductLineCategory($rawName)) {
            return response()->json([
                'status' => 'error',
                'message' => "El término '{$rawName}' corresponde a una línea de producto o tipo de prenda (categoría), no a una marca comercial.",
            ], 422);
        }

        $name = trim($validated['name']);
        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($name);

        $baseSlug = $slug;
        $counter = 1;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $brand = Brand::create([
            'name' => $name,
            'slug' => $slug,
            'supplier_id' => (int) $validated['supplier_id'],
            'description' => $validated['description'] ?? null,
            'logo_url' => $validated['logo_url'] ?? null,
            'website' => $validated['website'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_archived' => false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $supplier = Supplier::find($brand->supplier_id);
        if ($supplier) {
            $supplierBrands = is_array($supplier->brands) ? $supplier->brands : [];
            if (!in_array($brand->name, $supplierBrands, true)) {
                $supplierBrands[] = $brand->name;
                $supplier->brands = array_values(array_unique(array_filter($supplierBrands, fn($b) => !Supplier::isProductLineCategory($b))));
                $supplier->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Marca comercial '{$brand->name}' dada de alta y asociada al proveedor exitosamente.",
            'data' => [
                'id' => $brand->id,
                'supplier_id' => $brand->supplier_id,
                'supplier_name' => $supplier ? ($supplier->trade_name ?? $supplier->name) : null,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo_url' => $brand->logo_url,
                'website' => $brand->website,
                'is_active' => (bool)$brand->is_active,
                'is_archived' => (bool)$brand->is_archived,
                'supplier_brands_tree' => $supplier ? $supplier->getBrandsTree() : [],
            ],
        ], 201);
    }

    /**
     * Consulta de detalle de una marca.
     */
    public function show(int|string $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $brand = Brand::with('supplier')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $brand->id,
                'supplier_id' => $brand->supplier_id,
                'supplier' => $brand->supplier ? [
                    'id' => $brand->supplier->id,
                    'name' => $brand->supplier->name,
                    'tradeName' => $brand->supplier->trade_name ?? $brand->supplier->name,
                ] : null,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo_url' => $brand->logo_url,
                'website' => $brand->website,
                'is_active' => (bool)$brand->is_active,
                'is_archived' => (bool)$brand->is_archived,
                'sku_count' => Product::where('brand', $brand->name)->count(),
            ],
        ]);
    }

    /**
     * Actualización completa de la marca.
     */
    public function update(UpdateBrandRequest $request, int|string $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $validated = $request->validated();

        $oldSupplierId = $brand->supplier_id;
        $oldName = $brand->name;

        if (isset($validated['name'])) {
            $candidateName = trim($validated['name']);
            if (Supplier::isProductLineCategory($candidateName)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "El término '{$candidateName}' corresponde a una línea de producto o tipo de prenda (categoría), no a una marca comercial.",
                ], 422);
            }
            $brand->name = $candidateName;
            if (empty($validated['slug'])) {
                $baseSlug = Str::slug($brand->name);
                $slug = $baseSlug;
                $counter = 1;
                while (Brand::where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $brand->slug = $slug;
            }
        }

        if (array_key_exists('supplier_id', $validated)) {
            $supplierId = $validated['supplier_id'];
            $brand->supplier_id = (!empty($supplierId) && $supplierId !== 'unassigned') ? (int)$supplierId : null;
        }

        if (isset($validated['slug'])) {
            $brand->slug = Str::slug($validated['slug']);
        }
        if (array_key_exists('description', $validated)) {
            $brand->description = $validated['description'];
        }
        if (array_key_exists('logo_url', $validated)) {
            $brand->logo_url = $validated['logo_url'];
        }
        if (array_key_exists('website', $validated)) {
            $brand->website = $validated['website'];
        }
        if (array_key_exists('is_active', $validated)) {
            $brand->is_active = (bool)$validated['is_active'];
        }
        if (array_key_exists('sort_order', $validated)) {
            $brand->sort_order = (int)$validated['sort_order'];
        }

        $brand->save();

        if ($oldSupplierId && $oldSupplierId !== $brand->supplier_id) {
            $oldSupplier = Supplier::find($oldSupplierId);
            if ($oldSupplier && is_array($oldSupplier->brands)) {
                $oldSupplier->brands = array_values(array_filter($oldSupplier->brands, fn ($b) => $b !== $oldName));
                $oldSupplier->save();
            }
        }
        if ($brand->supplier_id) {
            $newSupplier = Supplier::find($brand->supplier_id);
            if ($newSupplier) {
                $currBrands = is_array($newSupplier->brands) ? $newSupplier->brands : [];
                if (!in_array($brand->name, $currBrands, true)) {
                    $currBrands[] = $brand->name;
                    $newSupplier->brands = array_values(array_unique($currBrands));
                    $newSupplier->save();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$brand->name}' actualizada correctamente.",
            'data' => [
                'id' => $brand->id,
                'supplier_id' => $brand->supplier_id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo_url' => $brand->logo_url,
                'website' => $brand->website,
                'is_active' => (bool)$brand->is_active,
                'is_archived' => (bool)$brand->is_archived,
            ],
        ]);
    }

    /**
     * Alternar estado activo/inactivo.
     */
    public function toggleActive(int|string $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $brand = Brand::findOrFail($id);
        $brand->is_active = !$brand->is_active;
        $brand->save();

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$brand->name}' ahora está " . ($brand->is_active ? 'Activa' : 'Inactiva') . '.',
            'data' => [
                'id' => $brand->id,
                'is_active' => (bool)$brand->is_active,
            ],
        ]);
    }

    /**
     * Archivar marca comercial.
     */
    public function archive(int|string $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $brand = Brand::findOrFail($id);
        $brand->is_archived = true;
        $brand->is_active = false;
        $brand->save();

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$brand->name}' archivada con éxito.",
            'data' => [
                'id' => $brand->id,
                'is_archived' => true,
                'is_active' => false,
            ],
        ]);
    }

    /**
     * Restaurar marca comercial archivada.
     */
    public function restore(int|string $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $brand = Brand::withTrashed()->findOrFail($id);
        $brand->is_archived = false;
        $brand->is_active = true;
        if ($brand->trashed()) {
            $brand->restore();
        }
        $brand->save();

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$brand->name}' restaurada a estado Activo.",
            'data' => [
                'id' => $brand->id,
                'is_archived' => false,
                'is_active' => true,
            ],
        ]);
    }

    /**
     * Eliminar marca comercial.
     */
    public function destroy(int|string $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $brand = Brand::findOrFail($id);
        $name = $brand->name;
        $supplierId = $brand->supplier_id;

        $brand->delete();

        if ($supplierId) {
            $supplier = Supplier::find($supplierId);
            if ($supplier && is_array($supplier->brands)) {
                $supplier->brands = array_values(array_filter($supplier->brands, fn ($b) => $b !== $name));
                $supplier->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$name}' eliminada con éxito.",
        ]);
    }

    /**
     * Asociar / Reasignar marca a un proveedor específico.
     */
    public function assignSupplier(Request $request, int|string $id): JsonResponse
    {
        Gate::authorize('manage-catalog');
        $brand = Brand::findOrFail($id);
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $brand->supplier_id = $validated['supplier_id'] ?? null;
        $brand->save();

        if ($brand->supplier_id) {
            $supplier = Supplier::find($brand->supplier_id);
            if ($supplier) {
                $currBrands = is_array($supplier->brands) ? $supplier->brands : [];
                if (!in_array($brand->name, $currBrands, true)) {
                    $currBrands[] = $brand->name;
                    $supplier->brands = array_values(array_unique($currBrands));
                    $supplier->save();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$brand->name}' asociada al proveedor exitosamente.",
            'data' => [
                'id' => $brand->id,
                'supplier_id' => $brand->supplier_id,
            ],
        ]);
    }

    protected function seedInitialBrands(): void
    {
        // ... (resto sin cambios significativos)
        $suppliers = Supplier::all();
        foreach ($suppliers as $sup) {
            $brandsList = is_array($sup->brands) ? $sup->brands : [];
            if (empty($brandsList)) {
                $brandsList = [$sup->trade_name ?? $sup->name];
            }
            foreach ($brandsList as $bName) {
                $bName = trim($bName);
                if (empty($bName) || Supplier::isProductLineCategory($bName)) continue;
                Brand::firstOrCreate(
                    ['name' => $bName],
                    [
                        'supplier_id' => $sup->id,
                        'slug' => Str::slug($bName),
                        'is_active' => $sup->status !== 'archived',
                        'is_archived' => $sup->status === 'archived',
                    ]
                );
            }
        }

        $productBrands = Product::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');

        foreach ($productBrands as $pBrand) {
            $pBrand = trim($pBrand);
            if (empty($pBrand) || Supplier::isProductLineCategory($pBrand)) continue;
            Brand::firstOrCreate(
                ['name' => $pBrand],
                [
                    'supplier_id' => null,
                    'slug' => Str::slug($pBrand),
                    'is_active' => true,
                    'is_archived' => false,
                ]
            );
        }
    }
}
