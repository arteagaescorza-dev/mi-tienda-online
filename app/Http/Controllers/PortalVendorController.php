<?php

namespace App\Http\Controllers;

use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierCreditAgreement;
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
}
