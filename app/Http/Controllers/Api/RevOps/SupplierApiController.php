<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SupplierApiController extends Controller
{
    /**
     * Listado de proveedores con estatus de integracion y acceso a Hub 7 (TASK-PUR-02 / SLICE 4).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query()
            ->withCount(['supplierProducts', 'users'])
            ->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        $suppliers = $query->get()->map(fn (Supplier $s) => $this->transformSupplier($s));

        return response()->json([
            'status' => 'success',
            'data' => $suppliers,
        ]);
    }

    /**
     * Registra un nuevo proveedor en la base de datos (TASK-PUR-02 / SLICE 4).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tradeName' => 'nullable|string|max:255',
            'legalName' => 'nullable|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'vendorCode' => 'nullable|string|max:50',
            'taxId' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'fiscalRegime' => 'nullable|string|max:150',
            'fiscal_regime' => 'nullable|string|max:150',
            'fiscalAddress' => 'nullable|string|max:500',
            'fiscal_address' => 'nullable|string|max:500',
            'fiscalZip' => 'nullable|string|max:20',
            'fiscal_zip' => 'nullable|string|max:20',
            'invoiceEmail' => 'nullable|email|max:150',
            'invoice_email' => 'nullable|email|max:150',
            'website' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'contactName' => 'required|string|max:150',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'leadTimeDays' => 'nullable|integer|min:1',
            'paymentTerms' => 'nullable|string|max:50',
            'extractionMethod' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:connected,paused,maintenance,archived',
            'origin' => 'nullable|string|in:manual,scraping',
            'brands' => 'nullable|array',
            'brands.*' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $name = trim($validated['name']);
        $tradeName = ! empty($validated['tradeName']) ? trim($validated['tradeName']) : $name;
        $legalName = ! empty($validated['legalName'] ?? $validated['legal_name'] ?? null) 
            ? trim($validated['legalName'] ?? $validated['legal_name']) 
            : $tradeName;
        
        $cleanCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $tradeName), 0, 4));
        if ($cleanCode === '') {
            $cleanCode = 'PROV';
        }
        $vendorCode = ! empty($validated['vendorCode']) 
            ? trim($validated['vendorCode']) 
            : 'VND-' . $cleanCode . '-' . rand(100, 999);

        $type = $validated['type'] ?? 'workshop';
        $dbType = match ($type) {
            'scraper' => 'dropship_scrape',
            'api' => 'dropship_api',
            'local', 'workshop', 'packaging' => 'local',
            default => 'wholesale',
        };

        $origin = $validated['origin'] ?? ($dbType === 'dropship_scrape' ? 'scraping' : 'manual');

        $brands = isset($validated['brands']) && is_array($validated['brands'])
            ? array_values(array_unique(array_filter(array_map('trim', $validated['brands']))))
            : [];

        if (empty($brands)) {
            $brands = [$tradeName];
        }

        $supplier = Supplier::create([
            'name' => $tradeName,
            'legal_name' => $legalName,
            'vendor_code' => $vendorCode,
            'tax_id' => ! empty($validated['taxId'] ?? $validated['tax_id'] ?? null) ? strtoupper(trim($validated['taxId'] ?? $validated['tax_id'])) : null,
            'fiscal_regime' => $validated['fiscalRegime'] ?? $validated['fiscal_regime'] ?? null,
            'fiscal_address' => $validated['fiscalAddress'] ?? $validated['fiscal_address'] ?? null,
            'fiscal_zip' => $validated['fiscalZip'] ?? $validated['fiscal_zip'] ?? null,
            'invoice_email' => ! empty($validated['invoiceEmail'] ?? $validated['invoice_email'] ?? null) ? trim($validated['invoiceEmail'] ?? $validated['invoice_email']) : null,
            'website' => ! empty($validated['website']) ? trim($validated['website']) : null,
            'contact_name' => trim($validated['contactName']),
            'phone' => trim($validated['phone']),
            'contact_email' => ! empty($validated['email']) ? trim($validated['email']) : ('contacto@' . Str::slug($tradeName) . '.com'),
            'city' => ! empty($validated['city']) ? trim($validated['city']) : 'CDMX',
            'type' => $dbType,
            'origin' => $origin,
            'lead_time_days' => (int) ($validated['leadTimeDays'] ?? 3),
            'payment_terms' => $validated['paymentTerms'] ?? 'contado',
            'status' => $validated['status'] ?? 'connected',
            'extraction_method' => $validated['extractionMethod'] ?? ($origin === 'manual' ? 'Alta Manual / Contacto Directo' : 'Scraping B2B Headless'),
            'sla_on_time_pct' => 100.00,
            'sla_fill_rate_pct' => 100.00,
            'avg_delay_days' => 0.00,
            'brands' => $brands,
            'notes' => $validated['notes'] ?? null,
        ]);

        $supplier->loadCount(['supplierProducts', 'users']);

        return response()->json([
            'status' => 'success',
            'message' => "Proveedor '{$supplier->name}' registrado exitosamente en el catalogo maestro.",
            'data' => $this->transformSupplier($supplier),
        ], 201);
    }

    /**
     * Muestra la ficha detallada de un proveedor.
     */
    public function show(int $id): JsonResponse
    {
        $supplier = Supplier::withCount(['supplierProducts', 'users'])
            ->with(['users:id,name,email,role,supplier_id'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformSupplier($supplier),
        ]);
    }

    /**
     * Actualiza las condiciones comerciales, contacto, datos fiscales y marcas de un proveedor.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'tradeName' => 'nullable|string|max:255',
            'legalName' => 'nullable|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'vendorCode' => 'nullable|string|max:50',
            'taxId' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'fiscalRegime' => 'nullable|string|max:150',
            'fiscal_regime' => 'nullable|string|max:150',
            'fiscalAddress' => 'nullable|string|max:500',
            'fiscal_address' => 'nullable|string|max:500',
            'fiscalZip' => 'nullable|string|max:20',
            'fiscal_zip' => 'nullable|string|max:20',
            'invoiceEmail' => 'nullable|email|max:150',
            'invoice_email' => 'nullable|email|max:150',
            'website' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'contactName' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'leadTimeDays' => 'nullable|integer|min:1',
            'paymentTerms' => 'nullable|string|max:50',
            'extractionMethod' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:connected,paused,maintenance,archived',
            'origin' => 'nullable|string|in:manual,scraping',
            'brands' => 'nullable|array',
            'brands.*' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $updateData = [];

        if (isset($validated['name']) || isset($validated['tradeName'])) {
            $updateData['name'] = trim($validated['tradeName'] ?? $validated['name']);
        }
        if (isset($validated['legalName']) || isset($validated['legal_name'])) {
            $updateData['legal_name'] = trim($validated['legalName'] ?? $validated['legal_name']);
        }
        if (isset($validated['vendorCode'])) {
            $updateData['vendor_code'] = trim($validated['vendorCode']);
        }
        if (isset($validated['taxId']) || isset($validated['tax_id'])) {
            $updateData['tax_id'] = strtoupper(trim($validated['taxId'] ?? $validated['tax_id']));
        }
        if (isset($validated['fiscalRegime']) || isset($validated['fiscal_regime'])) {
            $updateData['fiscal_regime'] = trim($validated['fiscalRegime'] ?? $validated['fiscal_regime']);
        }
        if (isset($validated['fiscalAddress']) || isset($validated['fiscal_address'])) {
            $updateData['fiscal_address'] = trim($validated['fiscalAddress'] ?? $validated['fiscal_address']);
        }
        if (isset($validated['fiscalZip']) || isset($validated['fiscal_zip'])) {
            $updateData['fiscal_zip'] = trim($validated['fiscalZip'] ?? $validated['fiscal_zip']);
        }
        if (isset($validated['invoiceEmail']) || isset($validated['invoice_email'])) {
            $updateData['invoice_email'] = trim($validated['invoiceEmail'] ?? $validated['invoice_email']);
        }
        if (isset($validated['website'])) {
            $updateData['website'] = trim($validated['website']);
        }
        if (isset($validated['contactName'])) {
            $updateData['contact_name'] = trim($validated['contactName']);
        }
        if (isset($validated['phone'])) {
            $updateData['phone'] = trim($validated['phone']);
        }
        if (isset($validated['email'])) {
            $updateData['contact_email'] = trim($validated['email']);
        }
        if (isset($validated['city'])) {
            $updateData['city'] = trim($validated['city']);
        }
        if (isset($validated['type'])) {
            $updateData['type'] = match ($validated['type']) {
                'scraper' => 'dropship_scrape',
                'api' => 'dropship_api',
                'local', 'workshop', 'packaging' => 'local',
                default => 'wholesale',
            };
        }
        if (isset($validated['origin'])) {
            $updateData['origin'] = $validated['origin'];
        }
        if (isset($validated['brands'])) {
            $updateData['brands'] = array_values(array_unique(array_filter(array_map('trim', $validated['brands']))));
        }
        if (isset($validated['leadTimeDays'])) {
            $updateData['lead_time_days'] = (int) $validated['leadTimeDays'];
        }
        if (isset($validated['paymentTerms'])) {
            $updateData['payment_terms'] = $validated['paymentTerms'];
        }
        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }
        if (isset($validated['extractionMethod'])) {
            $updateData['extraction_method'] = $validated['extractionMethod'];
        }
        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $supplier->update($updateData);
        $supplier->loadCount(['supplierProducts', 'users']);

        return response()->json([
            'status' => 'success',
            'message' => "Datos de '{$supplier->name}' actualizados correctamente.",
            'data' => $this->transformSupplier($supplier),
        ]);
    }

    /**
     * Alterna el estatus operativo del proveedor (connected, paused, maintenance, archived).
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:connected,paused,maintenance,archived',
        ]);

        $supplier->update(['status' => $validated['status']]);

        $labels = [
            'connected' => 'Conectado / Activo',
            'paused' => 'Pausado',
            'maintenance' => 'En Mantenimiento',
            'archived' => 'Archivado',
        ];

        return response()->json([
            'status' => 'success',
            'message' => "Estado de '{$supplier->name}' actualizado a {$labels[$supplier->status]}.",
            'data' => [
                'id' => $supplier->id,
                'status' => $supplier->status,
            ],
        ]);
    }

    /**
     * Elimina o archiva un proveedor del sistema.
     */
    public function destroy(int $id): JsonResponse
    {
        $supplier = Supplier::withCount(['supplierProducts', 'purchaseOrders'])->findOrFail($id);

        if ($supplier->supplier_products_count > 0 || $supplier->purchase_orders_count > 0) {
            $supplier->update(['status' => 'archived']);
            return response()->json([
                'status' => 'success',
                'action' => 'archived',
                'message' => "El proveedor tiene historico operativo y ha sido archivado en lugar de eliminado fisicamente.",
                'data' => [
                    'id' => $supplier->id,
                    'status' => 'archived',
                ],
            ]);
        }

        $name = $supplier->name;
        $supplier->delete();

        return response()->json([
            'status' => 'success',
            'action' => 'deleted',
            'message' => "Proveedor '{$name}' eliminado definitivamente del catalogo.",
            'data' => ['id' => $id],
        ]);
    }

    /**
     * Vincula o genera acceso para este proveedor en el Extranet B2B (Hub 7).
     */
    public function inviteExtranet(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $email = $supplier->contact_email ?: ('b2b@' . Str::slug($supplier->name) . '.com');
        $contactName = $supplier->contact_name ?: $supplier->name;

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'role' => 'supplier',
                'supplier_id' => $supplier->id,
            ]);
        } else {
            $user = User::create([
                'name' => $contactName,
                'email' => $email,
                'password' => Hash::make('Proveedor2026!'),
                'role' => 'supplier',
                'supplier_id' => $supplier->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Acceso a Extranet Hub 7 generado para {$supplier->name}. Usuario: {$user->email}",
            'data' => [
                'supplier_id' => $supplier->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'has_extranet_access' => true,
            ],
        ]);
    }

    /**
     * Transforma el modelo Supplier a la estructura canonica consumida por el frontend de Hub 4.
     */
    private function transformSupplier(Supplier $s): array
    {
        $hasExtranet = $s->relationLoaded('users') 
            ? $s->users->where('role', 'supplier')->isNotEmpty()
            : $s->users()->where('role', 'supplier')->exists();

        return [
            'id' => 'prov_' . $s->id,
            'dbId' => $s->id,
            'vendorCode' => $s->vendor_code ?: ('VND-' . strtoupper(Str::slug($s->name)) . '-001'),
            'name' => $s->name,
            'tradeName' => $s->name,
            'legalName' => $s->legal_name ?: $s->name,
            'taxId' => $s->tax_id ?: '',
            'fiscalRegime' => $s->fiscal_regime ?: '',
            'fiscalAddress' => $s->fiscal_address ?: '',
            'fiscalZip' => $s->fiscal_zip ?: '',
            'invoiceEmail' => $s->invoice_email ?: '',
            'website' => $s->website ?: '',
            'origin' => $s->origin ?: ($s->type === 'dropship_scrape' ? 'scraping' : 'manual'),
            'isManual' => ($s->origin === 'manual' || $s->type !== 'dropship_scrape'),
            'brands' => is_array($s->brands) ? $s->brands : [],
            'brandsTree' => $s->getBrandsTree(),
            'type' => $s->type,
            'typeLabel' => match ($s->type) {
                'dropship_scrape' => 'Fabricante Mayorista & Scraping B2B',
                'dropship_api' => 'Proveedor API B2B Integrado',
                'local' => 'Taller Local & Stock Propio',
                default => 'Distribuidor Mayorista',
            },
            'city' => $s->city ?: 'CDMX',
            'contactName' => $s->contact_name ?: 'Atencion Comercial',
            'phone' => $s->phone ?: '+52 55 0000 0000',
            'email' => $s->contact_email ?: ('b2b@' . Str::slug($s->name) . '.com'),
            'extractionMethod' => $s->extraction_method ?: 'Manual / Facturacion Directa',
            'scrape_frequency' => $s->scrape_frequency ?: 'every_6_hours',
            'frequency' => $s->scrape_frequency ?: 'every_6_hours',
            'lastScrapeTime' => 'Hace 18 min',
            'syncedItemsCount' => (int) ($s->supplier_products_count ?? $s->supplierProducts()->count()),
            'status' => $s->status ?: 'connected',
            'leadTimeDays' => (int) ($s->lead_time_days ?: 3),
            'paymentTerms' => $s->payment_terms ?: 'contado',
            'slaOnTimePct' => (float) ($s->sla_on_time_pct ?: 98.0),
            'slaFillRatePct' => (float) ($s->sla_fill_rate_pct ?: 98.0),
            'totalOrders' => 12,
            'completedOrders' => 11,
            'avgDelayDays' => (float) ($s->avg_delay_days ?: 0.0),
            'isSyncing' => false,
            'notes' => $s->notes ?: 'Proveedor oficial registrado.',
            'hasExtranetAccess' => $hasExtranet,
        ];
    }

    /**
     * Asocia una nueva marca al arbol de marcas del proveedor.
     */
    public function addBrand(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
        ]);

        $brand = trim($validated['brand']);
        if (Supplier::isProductLineCategory($brand)) {
            return response()->json([
                'status' => 'error',
                'message' => "El término '{$brand}' corresponde a una línea de producto o tipo de prenda (categoría), no a una marca comercial.",
            ], 422);
        }

        $brands = is_array($supplier->brands) ? $supplier->brands : [];

        if (! in_array($brand, $brands, true)) {
            $brands[] = $brand;
            $supplier->update(['brands' => array_values(array_filter($brands, fn($b) => !Supplier::isProductLineCategory($b)))]);
        }

        // REGLA 1: Sincronizar o crear en la tabla de marcas comerciales asociándola al proveedor
        $brandModel = \App\Models\Brand::where('name', $brand)->first();
        if ($brandModel) {
            $brandModel->supplier_id = $supplier->id;
            $brandModel->is_active = true;
            $brandModel->save();
        } else {
            \App\Models\Brand::create([
                'supplier_id' => $supplier->id,
                'name' => $brand,
                'slug' => \Illuminate\Support\Str::slug($brand),
                'is_active' => true,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$brand}' agregada exitosamente al proveedor '{$supplier->name}'.",
            'data' => $this->transformSupplier($supplier->fresh()),
        ]);
    }

    /**
     * Desvincula una marca del proveedor.
     */
    public function removeBrand(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
        ]);

        $brand = trim($validated['brand']);
        $brands = is_array($supplier->brands) ? $supplier->brands : [];
        $filtered = array_values(array_filter($brands, fn ($b) => $b !== $brand));

        $supplier->update(['brands' => $filtered]);

        // Sincronizar tabla brands: si pertenece a este proveedor, marcar supplier_id null o archivar
        \App\Models\Brand::where('supplier_id', $supplier->id)->where('name', $brand)->update(['supplier_id' => null]);

        return response()->json([
            'status' => 'success',
            'message' => "Marca '{$brand}' eliminada del proveedor '{$supplier->name}'.",
            'data' => $this->transformSupplier($supplier->fresh()),
        ]);
    }

    /**
     * Actualiza la frecuencia de scrapeo del proveedor (Regla 3).
     */
    public function updateFrequency(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'frequency' => 'required|string|max:60',
        ]);

        $supplier->update(['scrape_frequency' => $validated['frequency']]);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'frequency' => $validated['frequency'],
            'message' => "Frecuencia de actualización de '{$supplier->name}' actualizada exitosamente.",
            'data' => $this->transformSupplier($supplier->fresh()),
        ]);
    }
}

