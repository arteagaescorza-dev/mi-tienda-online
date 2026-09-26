<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingManifest;
use App\Models\PackageType;
use App\Models\RmaRequest;
use App\Models\RmaInspectionLog;
use App\Models\FraudRiskAssessment;
use Illuminate\Http\Request;

class PortalLogisticsController extends Controller
{
    public function index(Request $request)
    {
        $ordersCount = Order::count();

        // 1. Mesa de Expedición: Bultos en espera de recolección (status 'label_created' o 'packed')
        $rampShipments = Shipment::with(['order.fraudAssessment'])
            ->whereIn('status', ['label_created', 'packed'])
            ->orderBy('id', 'desc')
            ->get();

        // 2. Torre de Control & Rastreo: Envíos en tránsito nacional o entregados recientemente
        $trackingShipments = Shipment::with(['order', 'manifest'])
            ->whereIn('status', ['in_transit', 'out_for_delivery', 'delivered', 'failed_attempt', 'returned'])
            ->orderBy('id', 'desc')
            ->get();

        // 3. Garantías & RMA
        $rmaRequests = RmaRequest::with(['order', 'inspectionLogs'])
            ->orderBy('id', 'desc')
            ->get();

        // 4. Catálogo de Cajas & Cubicaje IATA 5000
        $packageTypes = PackageType::orderBy('id')->get();

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
            $boxName = $s->weight_volumetric_kg > 0.8 ? 'Caja Doble Combo (BOX-G03)' : ($s->weight_volumetric_kg > 0.35 ? 'Caja Estándar Venus (BOX-M02)' : 'Caja Micro Marte (BOX-P01)');

            return [
                'id' => 'PKG-' . $s->id,
                'db_id' => $s->id,
                'order_number' => $s->order?->order_number ?? ('ORD-' . $s->order_id),
                'customer' => $custName,
                'destination' => $cityName,
                'weight_kg' => (float)$s->weight_actual_kg,
                'dimensions' => $s->weight_volumetric_kg > 0.8 ? '30x20x10 cm' : ($s->weight_volumetric_kg > 0.35 ? '22x15x6 cm' : '18x12x5 cm'),
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
            'trackingList' => $trackingList,
            'rmaRequests' => $rmaRequests,
            'manifests' => $manifests,
            'ordersCount' => $ordersCount,
        ]);
    }

    public function closeManifest(Request $request)
    {
        $validated = $request->validate([
            'courier' => 'required|string',
            'driver_name' => 'required|string|max:100',
            'driver_badge_or_truck' => 'required|string|max:50',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'integer|exists:shipments,id',
        ]);

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

        return redirect()->route('portal.logistics')
            ->with('success', "Manifiesto {$manifestNumber} cerrado con éxito ({$manifest->total_packages} bultos entregados al chofer {$validated['driver_name']}).");
    }

    public function inspectRma(Request $request, RmaRequest $rmaRequest)
    {
        $validated = $request->validate([
            'inspector_name' => 'required|string|max:100',
            'has_hygiene_seal_intact' => 'nullable',
            'has_factory_tag' => 'nullable',
            'verdict' => 'required|in:approved,rejected_sanitary_violation',
            'technical_notes' => 'nullable|string|max:500',
            'resolution_type' => 'nullable|string|in:replacement_shipped,store_credit_wallet,refund',
        ]);

        $sealIntact = (bool)($request->has_hygiene_seal_intact ?? false);
        $tagIntact = (bool)($request->has_factory_tag ?? false);

        $verdict = $validated['verdict'];
        if (!$sealIntact && $verdict === 'approved') {
            $verdict = 'rejected_sanitary_violation';
        }

        RmaInspectionLog::create([
            'rma_id' => $rmaRequest->id,
            'inspector_name' => $validated['inspector_name'],
            'has_hygiene_seal_intact' => $sealIntact,
            'has_factory_tag' => $tagIntact,
            'verdict' => $verdict,
            'technical_notes' => $validated['technical_notes'] ?? ($verdict === 'approved' 
                ? 'Inspección aprobada. Precinto y sellos higiénicos íntegros. Reingreso contable autorizado.'
                : 'Inspección rechazada por violación a protocolo higiénico de ropa interior.'),
            'created_at' => now(),
        ]);

        if ($verdict === 'approved') {
            $resolution = $validated['resolution_type'] ?? 'replacement_shipped';
            $rmaRequest->update([
                'sanitary_status' => $resolution === 'store_credit_wallet' ? 'approved_store_credit' : 'approved_replacement',
                'resolution_type' => $resolution,
                'status' => 'resolved',
            ]);
            $msg = "RMA {$rmaRequest->rma_number} APROBADO: Sellos higiénicos válidos. Dictamen registrado y reemplazo procesado.";
        } else {
            $rmaRequest->update([
                'sanitary_status' => 'rejected_used_hygiene',
                'resolution_type' => null,
                'status' => 'rejected',
            ]);
            $msg = "RMA {$rmaRequest->rma_number} RECHAZADO: Violación de precinto sanitario. Se aplicó baja contable a merma por higiene.";
        }

        return redirect()->route('portal.logistics')
            ->with('success', $msg);
    }

    public function updatePackageStock(Request $request, PackageType $packageType)
    {
        $validated = $request->validate([
            'stock_available' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        $packageType->update($validated);

        return redirect()->route('portal.logistics')
            ->with('success', "Inventario de insumo {$packageType->name} ({$packageType->code}) actualizado a {$validated['stock_available']} unidades.");
    }

    public function updateShipmentStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:label_created,packed,in_transit,out_for_delivery,delivered,failed_attempt,returned',
        ]);

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
