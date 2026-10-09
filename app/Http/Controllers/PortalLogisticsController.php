<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingManifest;
use App\Models\PackageType;
use App\Models\RmaRequest;
use App\Models\RmaInspectionLog;
use App\Models\FraudRiskAssessment;
use App\Models\PackagingMovement;
use App\Services\RevOps\PackagingFulfillmentService;
use App\Services\RevOps\RmaResolutionService;
use Illuminate\Http\Request;
use App\Http\Requests\Portal\Logistics\IndexRequest;
use App\Http\Requests\Portal\Logistics\CloseManifestRequest;
use App\Http\Requests\Portal\Logistics\InspectRmaRequest;
use App\Http\Requests\Portal\Logistics\UpdatePackageStockRequest;
use App\Http\Requests\Portal\Logistics\FulfillPackagingRequest;
use App\Http\Requests\Portal\Logistics\UpdateShipmentStatusRequest;
use Illuminate\Support\Facades\Gate;


class PortalLogisticsController extends Controller
{
    public function __construct(
        protected PackagingFulfillmentService $packagingService = new PackagingFulfillmentService(),
        protected RmaResolutionService $rmaResolutionService = new RmaResolutionService()
    ) {}

    public function index(IndexRequest $request)
    {
        Gate::authorize('manage-logistics');

        $ordersCount = Order::count();

        // 1. Mesa de Expedición: Bultos en espera de recolección (status 'label_created' o 'packed')
        $rampShipments = Shipment::with(['order.fraudAssessment', 'packageType'])
            ->whereIn('status', ['label_created', 'packed'])
            ->orderBy('id', 'desc')
            ->get();

        // 2. Torre de Control & Rastreo: Envíos en tránsito nacional o entregados recientemente
        $trackingShipments = Shipment::with(['order', 'manifest', 'packageType'])
            ->whereIn('status', ['in_transit', 'out_for_delivery', 'delivered', 'failed_attempt', 'returned'])
            ->orderBy('id', 'desc')
            ->get();

        // 3. Garantías & RMA
        $rmaRequests = RmaRequest::with(['order', 'inspectionLogs', 'resolutions', 'replacementOrder', 'resolution'])
            ->orderBy('id', 'desc')
            ->get();

        // 4. Catálogo de Cajas & Cubicaje IATA 5000
        $packageTypes = PackageType::orderBy('id')->get();

        // 5. Kardex de Movimientos de Insumos de Empaque (TASK-INT-13)
        $packagingMovements = PackagingMovement::with(['packageType', 'shipment', 'order', 'user'])
            ->orderBy('id', 'desc')
            ->take(12)
            ->get();

        // Manifiestos recientes
        $manifests = ShippingManifest::withCount('shipments')->orderBy('id', 'desc')->take(10)->get();

        // Totales y KPIs
        $rampCount = $rampShipments->count();
        $rampWeight = round($rampShipments->sum('weight_actual_kg'), 2);
        $rampHoursLeft = '14:00 hrs';

        $volumetricWeightSum = $rampShipments->sum('weight_volumetric_kg');
        $approxVolumeM3 = round($volumetricWeightSum / 200, 3);

        $summary = [
            ['label' => 'Bultos en rampa', 'value' => "{$rampCount} listos", 'subtext' => 'Listos para transportista', 'badge' => 'En Rampa'],
            ['label' => 'Guías emitidas', 'value' => '100% OK', 'subtext' => 'Cero guías térmicas pendientes', 'badge' => '100% OK'],
            ['label' => 'Corte recolección', 'value' => $rampHoursLeft, 'subtext' => 'Recolección programada hoy', 'badge' => 'Corte Hoy'],
            ['label' => 'Carga en rampa', 'value' => "{$rampWeight} kg", 'subtext' => "~{$approxVolumeM3} m³ cúbicos", 'badge' => 'Peso Báscula'],
        ];

        // Format packages for view compatibility
        $packages = $rampShipments->map(function ($s) {
            $addr = $s->order?->shipping_address ?? [];
            $cityName = ($addr['city'] ?? 'CDMX') . ', ' . ($addr['state'] ?? 'CDMX') . ' (CP ' . ($addr['zip'] ?? '03100') . ')';
            $custName = $addr['name'] ?? ($s->order?->user?->name ?? 'Cliente Registrado');
            $fraud = $s->order?->fraudAssessment;
            $fraudScore = $fraud ? $fraud->risk_score : 10;
            
            $boxName = $s->packageType 
                ? "{$s->packageType->name} ({$s->packageType->code})"
                : ($s->weight_volumetric_kg > 0.8 ? 'Caja Doble Combo (BOX-G03)' : ($s->weight_volumetric_kg > 0.35 ? 'Caja Estándar Venus (BOX-M02)' : 'Caja Micro Marte (BOX-P01)'));

            return [
                'id' => 'PKG-' . $s->id,
                'db_id' => $s->id,
                'package_type_id' => $s->package_type_id,
                'package_code' => $s->packageType?->code,
                'packaging_fulfilled_at' => $s->packaging_fulfilled_at,
                'order_number' => $s->order?->order_number ?? ('ORD-' . $s->order_id),
                'customer' => $custName,
                'destination' => $cityName,
                'weight_kg' => (float)$s->weight_actual_kg,
                'dimensions' => $s->packageType ? "{$s->packageType->length_cm}x{$s->packageType->width_cm}x{$s->packageType->height_cm} cm" : ($s->weight_volumetric_kg > 0.8 ? '30x20x10 cm' : ($s->weight_volumetric_kg > 0.35 ? '22x15x6 cm' : '18x12x5 cm')),
                'volumetric_kg' => (float)$s->weight_volumetric_kg,
                'box_type' => $boxName,
                'courier' => match ($s->courier) {
                    'dhl_express' => 'DHL Express',
                    'estafeta' => 'Estafeta',
                    'fedex' => 'FedEx',
                    '99minutos' => '99 Minutos',
                    default => strtoupper($s->courier),
                },
                'courier_raw' => $s->courier,
                'tracking' => $s->tracking_number,
                'status' => $s->status,
                'fraud_score' => $fraudScore,
                'fraud_level' => $fraud ? $fraud->risk_level : 'normal',
                'created_at' => $s->created_at ? $s->created_at->format('H:i') : now()->format('H:i'),
            ];
        });

        // Format tracking list
        $trackingList = $trackingShipments->map(function ($s) {
            $addr = $s->order?->shipping_address ?? [];
            $cityName = ($addr['city'] ?? 'México') . ', ' . ($addr['state'] ?? 'MX');
            $custName = $addr['name'] ?? ($s->order?->user?->name ?? 'Cliente Registrado');

            $statusLabel = match ($s->status) {
                'out_for_delivery' => 'En Ruta de Entrega',
                'in_transit' => 'En Tránsito Nacional',
                'delivered' => 'Entregado al Cliente',
                'failed_attempt' => 'Intento Fallido',
                'returned' => 'Devuelto a Remitente',
                default => 'En Proceso Logístico',
            };

            $lastCheckpoint = match ($s->status) {
                'out_for_delivery' => 'En unidad de reparto final para entrega antes de 18:00 hrs',
                'in_transit' => 'Centro Operativo HUB CDMX / Clasificación automatizada',
                'delivered' => 'Entregado con firma digital en domicilio',
                'failed_attempt' => 'No hubo respuesta en timbre. Segundo intento programado',
                'returned' => 'Paquete devuelto a almacén central CDMX',
                default => 'Generación de guía e ingreso a red',
            };

            $sla = match ($s->status) {
                'delivered' => 'delivered',
                'out_for_delivery', 'in_transit' => 'ontime',
                default => 'warning',
            };

            return [
                'id' => $s->id,
                'tracking_number' => $s->tracking_number,
                'order_number' => $s->order?->order_number ?? ('ORD-' . $s->order_id),
                'customer' => $custName,
                'city' => $cityName,
                'courier' => match ($s->courier) {
                    'dhl_express' => 'DHL Express',
                    'estafeta' => 'Estafeta',
                    'fedex' => 'FedEx',
                    '99minutos' => '99 Minutos',
                    default => strtoupper($s->courier),
                },
                'status' => $s->status,
                'status_label' => $statusLabel,
                'last_checkpoint' => $lastCheckpoint,
                'eta' => $s->status === 'delivered' ? 'Entregado' : ($s->status === 'out_for_delivery' ? 'Hoy antes de las 18:00 hrs' : 'Mañana hábil'),
                'sla_status' => $sla,
                'dispatched_at' => $s->dispatched_at ? $s->dispatched_at->format('d/m H:i') : null,
            ];
        });

        return view('portal.logistics', [
            'summary' => $summary,
            'packages' => $packages,
            'packageTypes' => $packageTypes,
            'packagingMovements' => $packagingMovements,
            'trackingList' => $trackingList,
            'rmaRequests' => $rmaRequests,
            'manifests' => $manifests,
            'ordersCount' => $ordersCount,
        ]);
    }

    public function closeManifest(CloseManifestRequest $request)
    {
        Gate::authorize('manage-logistics');

        $validated = 

        $courierPrefix = match ($validated['courier']) {
            'dhl_express' => 'DHL',
            'estafeta' => 'EST',
            'fedex' => 'FDX',
            '99minutos' => '99M',
            default => 'MAN',
        };

        $manifestNumber = 'MAN-' . $courierPrefix . '-' . date('Ymd-His');

        $manifest = ShippingManifest::create([
            'manifest_number' => $manifestNumber,
            'courier' => $validated['courier'],
            'total_packages' => count($validated['shipment_ids']),
            'driver_name' => $validated['driver_name'],
            'driver_badge_or_truck' => $validated['driver_badge_or_truck'],
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        $shipments = Shipment::whereIn('id', $validated['shipment_ids'])->get();

        // Deduccion automatica de cajas e insumos en Kardex (TASK-INT-13)
        $deductedPackages = $this->packagingService->fulfillBatch($shipments, $request->user());

        foreach ($shipments as $shipment) {
            $shipment->update([
                'manifest_id' => $manifest->id,
                'status' => 'in_transit',
                'dispatched_at' => now(),
            ]);

            if ($shipment->order) {
                $shipment->order->update(['status' => 'shipped']);
            }
        }

        $packagingNote = $deductedPackages > 0 ? " y {$deductedPackages} insumos de empaque deducidos en Kardex" : "";

        return redirect()->route('portal.logistics')
            ->with('success', "Manifiesto {$manifestNumber} cerrado con éxito ({$manifest->total_packages} bultos entregados al chofer {$validated['driver_name']}{$packagingNote}).");
    }

    public function inspectRma(InspectRmaRequest $request, RmaRequest $rmaRequest)
    {
        Gate::authorize('manage-logistics');

        $validated = 

        $resType = match ($validated['resolution_type'] ?? 'replacement_order') {
            'store_credit_wallet', 'store_credit' => 'store_credit',
            'replacement_shipped', 'replacement_order' => 'replacement_order',
            default => 'replacement_order',
        };

        $result = $this->rmaResolutionService->inspectAndResolve($rmaRequest, [
            'inspector_name' => $validated['inspector_name'],
            'has_hygiene_seal_intact' => (bool)($request->has_hygiene_seal_intact ?? false),
            'has_factory_tag' => (bool)($request->has_factory_tag ?? false),
            'verdict' => $validated['verdict'],
            'resolution_type' => $resType,
            'technical_notes' => $validated['technical_notes'] ?? null,
            'replacement_sku' => $validated['replacement_sku'] ?? null,
            'credit_amount' => $validated['credit_amount'] ?? null,
        ], $request->user());

        return redirect()->route('portal.logistics')
            ->with('success', $result['message']);
    }

    public function updatePackageStock(UpdatePackageStockRequest $request, PackageType $packageType)
    {
        Gate::authorize('manage-logistics');

        $validated = 

        $this->packagingService->adjustStock(
            $packageType,
            (int)$validated['stock_available'],
            (float)$validated['unit_cost'],
            isset($validated['reorder_point']) ? (int)$validated['reorder_point'] : null,
            $request->user()
        );

        $rop = $validated['reorder_point'] ?? $packageType->reorder_point;

        return redirect()->route('portal.logistics')
            ->with('success', "Inventario de insumo {$packageType->name} ({$packageType->code}) actualizado a {$validated['stock_available']} unidades (ROP: {$rop}). Asiento registrado en Kardex.");
    }

    public function fulfillPackaging(FulfillPackagingRequest $request, Shipment $shipment)
    {
        Gate::authorize('manage-logistics');

        $validated = 

        $packageType = !empty($validated['package_type_id']) 
            ? PackageType::find($validated['package_type_id']) 
            : null;

        $movement = $this->packagingService->fulfillShipmentPackaging($shipment, $packageType, $request->user());

        if (!$movement) {
            return redirect()->route('portal.logistics')
                ->with('error', "No fue posible deducir el empaque para el envío {$shipment->tracking_number}.");
        }

        return redirect()->route('portal.logistics')
            ->with('success', "Empaque asignado y deducido en Kardex: 1 unidad de {$movement->packageType->name} para {$shipment->tracking_number}. Stock restante: {$movement->stock_after}.");
    }

    public function updateShipmentStatus(UpdateShipmentStatusRequest $request, Shipment $shipment)
    {
        Gate::authorize('manage-logistics');

        $validated = 

        $data = ['status' => $validated['status']];
        if ($validated['status'] === 'delivered') {
            $data['delivered_at'] = now();
            if ($shipment->order) {
                $shipment->order->update(['status' => 'delivered']);
            }
        } elseif ($validated['status'] === 'in_transit') {
            $data['dispatched_at'] = now();
            if ($shipment->order) {
                $shipment->order->update(['status' => 'shipped']);
            }
        }

        $shipment->update($data);

        return redirect()->route('portal.logistics')
            ->with('success', "Estado del envío {$shipment->tracking_number} actualizado a {$validated['status']}.");
    }
}
