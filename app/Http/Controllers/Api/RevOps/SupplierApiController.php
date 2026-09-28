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
            'vendorCode' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'contactName' => 'required|string|max:150',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'leadTimeDays' => 'nullable|integer|min:1',
            'paymentTerms' => 'nullable|string|max:50',
            'extractionMethod' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:connected,paused,maintenance,archived',
            'notes' => 'nullable|string',
        ]);

        $name = trim($validated['name']);
        $tradeName = ! empty($validated['tradeName']) ? trim($validated['tradeName']) : $name;
        
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

        $supplier = Supplier::create([
            'name' => $tradeName,
            'vendor_code' => $vendorCode,
            'contact_name' => trim($validated['contactName']),
            'phone' => trim($validated['phone']),
            'contact_email' => ! empty($validated['email']) ? trim($validated['email']) : ('contacto@' . Str::slug($tradeName) . '.com'),
            'city' => ! empty($validated['city']) ? trim($validated['city']) : 'CDMX',
            'type' => $dbType,
            'lead_time_days' => (int) ($validated['leadTimeDays'] ?? 3),
            'payment_terms' => $validated['paymentTerms'] ?? 'contado',
            'status' => $validated['status'] ?? 'connected',
            'extraction_method' => $validated['extractionMethod'] ?? 'Manual / Facturacion Directa',
            'sla_on_time_pct' => 100.00,
            'sla_fill_rate_pct' => 100.00,
            'avg_delay_days' => 0.00,
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
     * Actualiza las condiciones comerciales y datos de un proveedor.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'tradeName' => 'nullable|string|max:255',
            'vendorCode' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'contactName' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'leadTimeDays' => 'nullable|integer|min:1',
            'paymentTerms' => 'nullable|string|max:50',
            'extractionMethod' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:connected,paused,maintenance,archived',
            'notes' => 'nullable|string',
        ]);

        $updateData = [];

        if (isset($validated['name']) || isset($validated['tradeName'])) {
            $updateData['name'] = trim($validated['tradeName'] ?? $validated['name']);
        }
        if (isset($validated['vendorCode'])) {
            $updateData['vendor_code'] = trim($validated['vendorCode']);
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
            'frequency' => 'Automatico (Cada 6 hrs)',
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
}
