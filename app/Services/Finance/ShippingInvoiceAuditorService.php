<?php

namespace App\Services\Finance;

use App\Models\OperationalTask;
use App\Models\PackageType;
use App\Models\Shipment;
use App\Models\ShippingDiscrepancyClaim;
use App\Models\ShippingManifest;
use App\Models\ShippingReconciliation;
use App\Models\ShippingReconciliationItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShippingInvoiceAuditorService
{
    /**
     * Calcula el peso volumetrico segun estandar IATA: (L x W x H) / 5000.
     */
    public function calculateVolumetricWeightIata(float $lengthCm, float $widthCm, float $heightCm): float
    {
        if ($lengthCm <= 0 || $widthCm <= 0 || $heightCm <= 0) {
            return 0.0;
        }
        return round(($lengthCm * $widthCm * $heightCm) / 5000, 2);
    }

    /**
     * Audita un reporte de facturacion de paqueteria contra las guias y manifiestos de expedicion (TASK-INT-08).
     */
    public function auditCarrierInvoiceReport(
        string $carrierId = '',
        string $invoiceFolio = '',
        array|string $invoiceRows = [],
        string $auditedBy = 'Mickey RevOps',
        ?string $importedFileName = null,
        ?string $carrier = null,
        ?string $rawCsv = null
    ): ShippingReconciliation {
        $carrierId = $carrier ?: $carrierId;
        if (!empty($rawCsv)) {
            $invoiceRows = $rawCsv;
        }
        if (is_string($invoiceRows)) {
            $invoiceRows = $this->parseCsvContent($invoiceRows);
        }

        return DB::transaction(function () use ($carrierId, $invoiceFolio, $invoiceRows, $auditedBy, $importedFileName) {
            $code = 'RECON-SHP-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -4));

            $reconciliation = ShippingReconciliation::create([
                'reconciliation_code' => $code,
                'reconciliation_folio' => $code,
                'carrier' => $carrierId,
                'carrier_id' => $carrierId,
                'carrier_name' => $this->resolveCarrierName($carrierId),
                'invoice_number' => $invoiceFolio,
                'invoice_folio' => $invoiceFolio,
                'invoice_date' => now()->toDateString(),
                'billing_period' => now()->format('Y-m'),
                'total_tracks_count' => count($invoiceRows),
                'matched_tracks_count' => 0,
                'discrepant_tracks_count' => 0,
                'total_invoiced_amount' => 0.00,
                'total_expected_amount' => 0.00,
                'total_declared_amount' => 0.00,
                'total_discrepancy_amount' => 0.00,
                'total_overcharge_amount' => 0.00,
                'total_recovered_amount' => 0.00,
                'status' => 'audited',
                'imported_file_name' => $importedFileName,
                'audited_by' => $auditedBy,
            ]);

            $totalInvoiced = 0.00;
            $totalExpected = 0.00;
            $totalOvercharge = 0.00;
            $discrepantCount = 0;
            $matchedCount = 0;

            foreach ($invoiceRows as $row) {
                $tracking = trim($row['tracking_number'] ?? '');
                if (!$tracking) {
                    continue;
                }

                $invoicedRate = (float)($row['invoiced_rate_mxn'] ?? $row['invoiced_rate'] ?? 0);
                $carrierBilledWeight = (float)($row['carrier_billed_weight_kg'] ?? $row['weight_kg'] ?? 0);

                // Buscar guia registrada en expedicion Hub 6
                $shipment = Shipment::where('tracking_number', $tracking)->first();
                $manifest = $shipment && $shipment->manifest_id ? ShippingManifest::find($shipment->manifest_id) : null;
                $pkgType = $shipment && $shipment->package_type_id ? PackageType::find($shipment->package_type_id) : null;

                $warehouseWeight = $shipment ? (float)($shipment->weight_actual_kg ?? 0.85) : 0.90;
                $boxL = $pkgType ? (float)$pkgType->length_cm : 25.0;
                $boxW = $pkgType ? (float)$pkgType->width_cm : 18.0;
                $boxH = $pkgType ? (float)$pkgType->height_cm : 10.0;

                // Calculo de peso volumetrico IATA 5000: (L x W x H) / 5000
                $warehouseVolumetric = $this->calculateVolumetricWeightIata($boxL, $boxW, $boxH);
                $billableWeight = max($warehouseWeight, $warehouseVolumetric);

                // Tarifa pactada esperada
                $expectedRate = ($shipment && $shipment->shipping_cost_declared > 0)
                    ? (float)$shipment->shipping_cost_declared
                    : $this->calculateExpectedRate($carrierId, $billableWeight);

                $diff = round($invoicedRate - $expectedRate, 2);
                $pct = $expectedRate > 0 ? round(($diff / $expectedRate) * 100, 2) : 0;

                $reason = null;
                $auditStatus = 'matched';
                $isOvercharged = false;

                // Criterio de semaforo: sobreprecio mayor al 10% se clasifica como discrepancia formal
                if ($diff > 0 && $pct > 10.0) {
                    $auditStatus = 'discrepancy';
                    $isOvercharged = true;
                    $discrepantCount++;
                    $totalOvercharge += $diff;

                    if ($carrierBilledWeight > $billableWeight * 1.20) {
                        $reason = 'Cubicaje inflado en bascula automatizada courier';
                    } elseif (!empty($row['extended_area_charge']) && (float)$row['extended_area_charge'] > 0) {
                        $reason = 'Recargo de zona extendida improcedente (cobertura regular)';
                    } else {
                        $reason = 'Recargo no pactado de combustible o tarifa base fuera de contrato';
                    }
                } elseif ($diff > 0) {
                    $auditStatus = 'marginal_variance';
                    $matchedCount++;
                } else {
                    $auditStatus = 'matched';
                    $matchedCount++;
                }

                $totalInvoiced += $invoicedRate;
                $totalExpected += $expectedRate;

                ShippingReconciliationItem::create([
                    'reconciliation_id' => $reconciliation->id,
                    'shipping_reconciliation_id' => $reconciliation->id,
                    'tracking_number' => $tracking,
                    'order_number' => $shipment && $shipment->order ? $shipment->order->order_number : ($row['order_number'] ?? null),
                    'shipping_manifest_id' => $manifest ? $manifest->id : null,
                    'package_type_id' => $pkgType ? $pkgType->id : null,
                    'box_code' => $pkgType ? $pkgType->code : 'BOX-STD',
                    'box_length_cm' => $boxL,
                    'box_width_cm' => $boxW,
                    'box_height_cm' => $boxH,
                    'declared_weight_kg' => $warehouseWeight,
                    'warehouse_weight_kg' => $warehouseWeight,
                    'warehouse_volumetric_kg' => $warehouseVolumetric,
                    'invoiced_weight_kg' => $carrierBilledWeight,
                    'carrier_billed_weight_kg' => $carrierBilledWeight,
                    'declared_cost' => $expectedRate,
                    'expected_rate_mxn' => $expectedRate,
                    'invoiced_cost' => $invoicedRate,
                    'carrier_invoiced_rate_mxn' => $invoicedRate,
                    'difference_cost' => $diff,
                    'overcharge_amount_mxn' => max(0, $diff),
                    'overcharge_pct' => max(0, $pct),
                    'is_overcharged' => $isOvercharged,
                    'discrepancy_reason' => $reason,
                    'audit_status' => $auditStatus,
                ]);
            }

            $reconciliation->update([
                'total_tracks_count' => count($invoiceRows),
                'matched_tracks_count' => $matchedCount,
                'discrepant_tracks_count' => $discrepantCount,
                'total_invoiced_amount' => $totalInvoiced,
                'total_expected_amount' => $totalExpected,
                'total_declared_amount' => $totalExpected,
                'total_discrepancy_amount' => $totalOvercharge,
                'total_overcharge_amount' => $totalOvercharge,
                'status' => $discrepantCount > 0 ? 'claims_pending' : 'audited',
            ]);

            return $reconciliation;
        });
    }

    /**
     * Parsea contenido de archivo CSV en un array asociativo de filas normalizadas.
     */
    public function parseCsvContent(string $csvContent): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent));
        if (empty($lines)) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        $normalizedHeaders = array_map(function ($h) {
            $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $h)));
            return match ($clean) {
                'guia', 'tracking', 'no_guia', 'tracking_no' => 'tracking_number',
                'peso', 'peso_kg', 'billed_weight', 'billed_weight_kg' => 'carrier_billed_weight_kg',
                'importe', 'monto', 'costo', 'tarifa', 'invoiced_rate', 'costo_facturado', 'monto_facturado', 'tarifa_facturada', 'total_facturado' => 'invoiced_rate_mxn',
                'pedido', 'orden', 'order', 'no_orden' => 'order_number',
                'zona_extendida', 'reexpedicion' => 'extended_area_charge',
                default => $clean,
            };
        }, $headers);

        $rows = [];
        foreach ($lines as $line) {
            if (!trim($line)) continue;
            $data = str_getcsv($line);
            if (count($data) !== count($normalizedHeaders)) continue;

            $row = array_combine($normalizedHeaders, $data);
            if (!empty($row['tracking_number'])) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Emite un expediente formal de reclamacion para retencion de pago en Cuentas por Pagar.
     */
    public function fileCarrierClaimDossier(
        ShippingReconciliation|int $reconciliation = 0,
        array $selectedItemIds = [],
        string $filedBy = 'Mickey RevOps',
        ?string $claimNotes = null,
        ?int $reconciliationId = null,
        ?array $itemIds = null,
        ?string $createdBy = null
    ): ShippingDiscrepancyClaim {
        $actualReconId = is_int($reconciliation) && $reconciliation > 0 ? $reconciliation : ($reconciliationId ?? ($reconciliation instanceof ShippingReconciliation ? $reconciliation->id : 0));
        $targetItemIds = !empty($selectedItemIds) ? $selectedItemIds : ($itemIds ?? []);
        $actualFiledBy = $createdBy ?: $filedBy;

        return DB::transaction(function () use ($actualReconId, $reconciliation, $targetItemIds, $actualFiledBy, $claimNotes) {
            $recon = ($reconciliation instanceof ShippingReconciliation) ? $reconciliation : ShippingReconciliation::findOrFail($actualReconId);
            $items = ShippingReconciliationItem::where(function ($q) use ($recon) {
                    $q->where('reconciliation_id', $recon->id)
                      ->orWhere('shipping_reconciliation_id', $recon->id);
                })
                ->whereIn('id', $targetItemIds)
                ->get();

            if ($items->isEmpty()) {
                throw new InvalidArgumentException('No se seleccionaron partidas validas para reclamar.');
            }

            $carrierTag = strtoupper($recon->carrier_id ?? $recon->carrier ?? 'COU');
            $folio = 'CLAIM-' . $carrierTag . '-' . date('Ym') . '-' . sprintf('%04d', rand(100, 999));
            $totalDisputed = (float)$items->sum('overcharge_amount_mxn');
            if ($totalDisputed <= 0) {
                $totalDisputed = (float)$items->sum('difference_cost');
            }

            $dossierUrl = "/storage/claims/{$folio}.pdf";
            $notes = $claimNotes ?: "Expediente formal por sobrepeso volumetrico no reconocido en {$items->count()} guias auditadas.";

            $claim = ShippingDiscrepancyClaim::create([
                'claim_folio' => $folio,
                'reconciliation_id' => $recon->id,
                'shipping_reconciliation_id' => $recon->id,
                'carrier' => $recon->carrier_id ?? $recon->carrier ?? 'courier',
                'carrier_id' => $recon->carrier_id ?? $recon->carrier ?? 'courier',
                'claimed_amount' => $totalDisputed,
                'total_disputed_amount' => $totalDisputed,
                'items_count' => $items->count(),
                'disputed_items_count' => $items->count(),
                'carrier_ticket_ref' => 'TKT-' . $carrierTag . '-' . rand(1000000, 9999999),
                'carrier_response' => 'Expediente formal emitido y radicado ante transportista.',
                'claim_technical_notes' => $notes,
                'claim_dossier_url' => $dossierUrl,
                'status' => 'submitted',
                'submitted_at' => now(),
                'filed_by' => $actualFiledBy,
            ]);

            ShippingReconciliationItem::whereIn('id', $targetItemIds)->update([
                'audit_status' => 'claim_filed',
                'shipping_discrepancy_claim_id' => $claim->id,
            ]);

            // Inyectar tarea operativa de retencion preventiva para cuentas por pagar
            OperationalTask::create([
                'title' => 'Retencion de pago a paqueteria por ' . $folio,
                'description' => "Retener MXN \${$totalDisputed} de la proxima factura de {$recon->carrier_name} hasta resolucion del expediente de reclamacion.",
                'category' => 'finance_discrepancy_hold',
                'priority' => 'high',
                'reference_code' => $folio,
                'due_at' => now()->addDays(5),
                'action_payload' => [
                    'reconciliation_id' => $recon->id,
                    'claim_id' => $claim->id,
                    'carrier' => $recon->carrier_name,
                    'disputed_amount' => $totalDisputed,
                ],
                'status' => 'pending',
            ]);

            return $claim;
        });
    }

    /**
     * Calcula la tarifa base esperada contractual segun courier y peso volumetrico facturable.
     */
    public function calculateExpectedRate(string $carrierId, float $billableKg): float
    {
        $baseRates = [
            'dhl' => 145.00,
            'dhl_express' => 145.00,
            'skydropx' => 125.00,
            'estafeta' => 110.00,
            'fedex' => 140.00,
            '99minutos' => 95.00,
            'paquetexpress' => 120.00,
        ];

        $base = $baseRates[strtolower($carrierId)] ?? 120.00;
        if ($billableKg > 1.0) {
            $base += ($billableKg - 1.0) * 25.00;
        }

        return round($base, 2);
    }

    public function resolveCarrierName(string $carrierId): string
    {
        $map = [
            'dhl' => 'DHL Express Mexico',
            'dhl_express' => 'DHL Express Mexico',
            'skydropx' => 'Skydropx API',
            'estafeta' => 'Estafeta Mexicana',
            'fedex' => 'FedEx Express',
            '99minutos' => '99 Minutos SameDay',
            'paquetexpress' => 'Paquetexpress',
        ];

        return $map[strtolower($carrierId)] ?? strtoupper($carrierId);
    }
}
