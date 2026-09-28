<?php

namespace App\Http\Controllers;

use App\Models\OperationalTask;
use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierCreditAgreement;
use App\Models\VendorShipment;
use App\Models\WarehouseRampEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalVendorController extends Controller
{
    /**
     * Resuelve el ID del proveedor autorizado garantizando el aislamiento de datos (TASK-HUB-VND).
     */
    protected function resolveAuthorizedSupplierId(Request $request): int
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'No autenticado.');
        }

        $activeRole = session('active_role') ?: $user->role;
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);

        if ($activeRole === 'vendor') {
            $supplierId = (int) ($request->attributes->get('current_supplier_id') ?: $user->supplier_id);
            if (empty($supplierId)) {
                abort(403, 'Acceso denegado: Usuario proveedor sin empresa proveedora vinculada.');
            }
            return $supplierId;
        }

        if ($isGlobalAdmin) {
            if ($request->filled('supplier_id')) {
                return (int) $request->query('supplier_id');
            }
            return (int) (Supplier::where('is_active', true)->first()?->id ?? Supplier::first()?->id ?? 1);
        }

        abort(403, 'Acceso restringido unicamente a proveedores o administradores.');
    }

    /**
     * Muestra la pantalla principal de la Extranet de Proveedores en Hub 7 (TASK-HUB-VND).
     */
    public function index(Request $request): View
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);
        $supplier = Supplier::findOrFail($supplierId);

        $orders = PurchaseOrder::where('supplier_id', $supplierId)
            ->with(['items.product', 'items.variant', 'creditAgreement'])
            ->latest()
            ->get();

        $creditAgreements = SupplierCreditAgreement::where('supplier_id', $supplierId)
            ->latest()
            ->get();

        $kpis = [
            'total_orders' => $orders->count(),
            'pending_confirmation' => $orders->whereIn('status', ['draft', 'sent'])->count(),
            'in_production' => $orders->whereIn('status', ['confirmed', 'in_transit'])->count(),
            'completed_orders' => $orders->where('status', 'received')->count(),
            'total_units' => (int) $orders->sum('total_units'),
            'total_invoiced' => (float) $orders->sum('total_amount'),
            'pending_credits_amount' => (float) $creditAgreements->where('status', 'pending')->sum('amount'),
            'applied_credits_count' => $creditAgreements->where('status', 'applied')->count(),
        ];

        $user = $request->user();
        $activeRole = session('active_role') ?: ($user ? $user->role : 'vendor');
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);
        $allSuppliers = $isGlobalAdmin ? Supplier::orderBy('name')->get() : collect([$supplier]);

        return view('portal.hub7-vendor-extranet', compact(
            'supplier',
            'orders',
            'creditAgreements',
            'kpis',
            'allSuppliers',
            'isGlobalAdmin',
            'activeRole'
        ));
    }

    /**
     * Endpoint API: Lista de Ordenes de Compra del proveedor autenticado.
     */
    public function getOrders(Request $request): JsonResponse
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);

        $orders = PurchaseOrder::where('supplier_id', $supplierId)
            ->with(['items.product', 'items.variant', 'creditAgreement'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'supplier_id' => $supplierId,
            'orders_count' => $orders->count(),
            'orders' => $orders,
        ]);
    }

    /**
     * Endpoint API: Detalle completo de una Orden de Compra especifica.
     */
    public function getOrderDetail(Request $request, int $id): JsonResponse
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);
        $order = PurchaseOrder::with(['items.product', 'items.variant', 'creditAgreement', 'supplier'])->findOrFail($id);

        $user = $request->user();
        $activeRole = session('active_role') ?: ($user ? $user->role : 'vendor');
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);

        if (!$isGlobalAdmin && (int) $order->supplier_id !== $supplierId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para consultar esta orden de compra.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Endpoint API: Confirma la aceptacion y programacion de la OC por parte del taller/fabricante.
     */
    public function confirmOrder(Request $request, int $id): JsonResponse
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);
        $order = PurchaseOrder::with('supplier')->findOrFail($id);

        $user = $request->user();
        $activeRole = session('active_role') ?: ($user ? $user->role : 'vendor');
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);

        if (!$isGlobalAdmin && (int) $order->supplier_id !== $supplierId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para confirmar esta orden de compra.',
            ], 403);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'No es posible confirmar una orden de compra cancelada.',
            ], 422);
        }

        $order->status = 'confirmed';
        $order->save();

        PurchaseKardex::create([
            'purchase_order_id' => $order->id,
            'supplier_id' => $order->supplier_id,
            'movement_type' => 'PO_CONFIRMED_BY_VENDOR',
            'description' => sprintf(
                'Orden de Compra %s confirmada por el fabricante %s. Aceptacion registrada en Extranet B2B.',
                $order->po_number,
                $order->supplier?->name ?? 'Proveedor'
            ),
            'total_amount' => (float) $order->total_amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Orden de compra {$order->po_number} confirmada exitosamente. En proceso de confeccion.",
            'order' => $order,
        ]);
    }

    /**
     * Vista de impresion formal / PDF de la Orden de Compra para el taller.
     */
    public function printOrder(Request $request, int $id): View
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);
        $order = PurchaseOrder::with(['items.product', 'items.variant', 'creditAgreement', 'supplier'])->findOrFail($id);

        $user = $request->user();
        $activeRole = session('active_role') ?: ($user ? $user->role : 'vendor');
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);

        if (!$isGlobalAdmin && (int) $order->supplier_id !== $supplierId) {
            abort(403, 'No autorizado para visualizar el documento de esta orden de compra.');
        }

        $snapshot = $order->po_snapshot ?: [];

        return view('portal.vendor-po-print', compact('order', 'snapshot'));
    }

    /**
     * Endpoint API: Consulta de Acuerdos de Credito y Minutas de Discrepancia del proveedor.
     */
    public function getCreditAgreements(Request $request): JsonResponse
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);

        $agreements = SupplierCreditAgreement::where('supplier_id', $supplierId)
            ->with(['appliedToPurchaseOrder'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'supplier_id' => $supplierId,
            'agreements_count' => $agreements->count(),
            'agreements' => $agreements,
        ]);
    }

    /**
     * Registra el despacho / remision fletera B2B de una orden de compra (TASK-INT-20).
     * Sincroniza el estatus de la OC a 'in_transit', registra la entrada en rampa de bodega y crea tarea para el almacenista.
     */
    public function dispatchOrder(Request $request, int $id): JsonResponse
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);
        $order = PurchaseOrder::with('supplier')->findOrFail($id);

        $user = $request->user();
        $activeRole = session('active_role') ?: ($user ? $user->role : 'vendor');
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);

        if (!$isGlobalAdmin && (int) $order->supplier_id !== $supplierId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para registrar despacho de esta orden de compra.',
            ], 403);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'No es posible despachar una orden cancelada.',
            ], 422);
        }

        $validated = $request->validate([
            'carrier_name' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'remission_number' => 'nullable|string|max:80',
            'packages_count' => 'nullable|integer|min:1',
            'driver_name' => 'nullable|string|max:100',
            'license_plate' => 'nullable|string|max:30',
            'dispatched_units' => 'nullable|integer|min:1',
            'estimated_arrival_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $dispatchedUnits = $validated['dispatched_units'] ?? $order->total_units;
        $packagesCount = $validated['packages_count'] ?? 1;

        // 1. Registro de Remision Fletera B2B (vendor_shipments)
        $shipment = VendorShipment::create([
            'po_id' => (string) $order->id,
            'po_code' => $order->po_number,
            'supplier_id' => (string) $order->supplier_id,
            'carrier_name' => $validated['carrier_name'],
            'tracking_number' => $validated['tracking_number'],
            'pieces_dispatched' => $dispatchedUnits,
            'remission_number' => $validated['remission_number'] ?? null,
            'status' => 'in_transit',
            'dispatched_at' => now(),
        ]);

        // 2. Actualizar Orden de Compra
        $order->status = 'in_transit';
        $order->carrier = $validated['carrier_name'];
        $order->tracking_number = $validated['tracking_number'];
        $order->save();

        // 3. Asiento Inmutable en Kardex de Compras
        PurchaseKardex::create([
            'purchase_order_id' => $order->id,
            'supplier_id' => $order->supplier_id,
            'movement_type' => 'PO_DISPATCHED_BY_VENDOR',
            'description' => sprintf(
                'Aviso de despacho B2B por %s: Remision %s, Transportista %s, Guia %s (%d piezas en %d bultos).',
                $order->supplier?->name ?? 'Fabricante',
                $validated['remission_number'] ?? 'S/R',
                $validated['carrier_name'],
                $validated['tracking_number'],
                $dispatchedUnits,
                $packagesCount
            ),
            'total_amount' => (float) $order->total_amount,
        ]);

        // 4. Sincronizar Entrada Programada en Rampa de Bodega (Hub 4)
        $cleanPo = preg_replace('/[^A-Za-z0-9]/', '', $order->po_number);
        $rampEntry = WarehouseRampEntry::create([
            'entry_folio' => 'RAMP-' . strtoupper(substr($cleanPo, 0, 15)) . '-' . rand(100, 999),
            'carrier_or_supplier' => $validated['carrier_name'] . ' / ' . ($order->supplier?->name ?? 'Proveedor'),
            'driver_name' => $validated['driver_name'] ?? 'Operador Transportista',
            'license_plate' => $validated['license_plate'] ?? null,
            'packages_received_count' => $packagesCount,
            'ramp_number' => 'RAMPA_01',
            'status' => 'in_dock',
            'docked_at' => now(),
        ]);

        // 5. Tarea Operativa para el Jefe de Almacen en Hub 1
        $task = OperationalTask::create([
            'title' => "Recepcion Inbound en Rampa: {$order->po_number} ({$dispatchedUnits} uds)",
            'category' => 'almacen',
            'priority' => ($order->priority === 'critical' || $order->priority === 'urgent') ? 'critical' : 'high',
            'assigned_role' => 'warehouse',
            'status' => 'pending',
            'sla_minutes_limit' => 240,
            'due_at' => now()->addHours(4),
            'reference_code' => $order->po_number,
            'description' => sprintf(
                'El fabricante %s reporto envio mediante %s (Guia: %s, Remision: %s). Preparar rampa para descarga y cotejo de %d bultos.',
                $order->supplier?->name ?? 'Proveedor',
                $validated['carrier_name'],
                $validated['tracking_number'],
                $validated['remission_number'] ?? 'S/R',
                $packagesCount
            ),
            'action_payload' => [
                'purchase_order_id' => $order->id,
                'po_number' => $order->po_number,
                'supplier_id' => $order->supplier_id,
                'supplier_name' => $order->supplier?->name,
                'carrier' => $validated['carrier_name'],
                'tracking_number' => $validated['tracking_number'],
                'remission_number' => $validated['remission_number'] ?? null,
                'packages_count' => $packagesCount,
                'dispatched_units' => $dispatchedUnits,
                'ramp_entry_id' => $rampEntry->id,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Despacho registrado exitosamente para la orden {$order->po_number}. Notificacion enviada a rampa de bodega.",
            'order' => $order,
            'shipment' => $shipment,
            'ramp_entry' => $rampEntry,
            'task' => $task,
        ]);
    }

    /**
     * Endpoint API: Consulta el detalle de remision / envio fletero de una orden.
     */
    public function getShipment(Request $request, int $id): JsonResponse
    {
        $supplierId = $this->resolveAuthorizedSupplierId($request);
        $order = PurchaseOrder::findOrFail($id);

        $user = $request->user();
        $activeRole = session('active_role') ?: ($user ? $user->role : 'vendor');
        $isGlobalAdmin = in_array($activeRole, ['superadmin', 'admin', 'all_roles'], true);

        if (!$isGlobalAdmin && (int) $order->supplier_id !== $supplierId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para consultar el despacho de esta orden.',
            ], 403);
        }

        $shipment = VendorShipment::where('po_id', (string) $order->id)->latest()->first();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'po_number' => $order->po_number,
            'shipment' => $shipment,
        ]);
    }
}
