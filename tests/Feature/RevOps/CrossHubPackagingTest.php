<?php

namespace Tests\Feature\RevOps;

use App\Models\OperationalTask;
use App\Models\Order;
use App\Models\PackageType;
use App\Models\PackagingMovement;
use App\Models\Shipment;
use App\Models\User;
use App\Services\RevOps\PackagingFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossHubPackagingTest extends TestCase
{
    use RefreshDatabase;

    private function portalUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'operativo.crosshub@mitienda.test'],
            [
                'name' => 'Operador CrossHub',
                'password' => bcrypt('password'),
                'role' => 'portal',
            ]
        );
    }

    public function test_closing_manifest_automatically_deducts_packaging_in_kardex(): void
    {
        $packageType = PackageType::create([
            'code' => 'BOX-P01',
            'name' => 'Caja Chica D2C',
            'type' => 'box',
            'length_cm' => 20,
            'width_cm' => 15,
            'height_cm' => 8,
            'max_weight_kg' => 2.0,
            'unit_cost' => 12.50,
            'stock_available' => 100,
            'reorder_point' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-AUTO-PACK-001',
            'status' => 'processing',
            'total' => 699.00,
            'shipping_address' => ['city' => 'CDMX', 'state' => 'CDMX', 'zip' => '03100', 'name' => 'Cliente CrossHub'],
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'DHL-CROSS-1001',
            'courier' => 'dhl_express',
            'status' => 'packed',
            'weight_actual_kg' => 0.40,
            'weight_volumetric_kg' => 0.25,
            'shipping_cost_declared' => 140.00,
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.logistics.manifests.close'), [
                'courier' => 'dhl_express',
                'driver_name' => 'Carlos Chofer DHL',
                'driver_badge_or_truck' => 'DHL-CAM-889',
                'shipment_ids' => [$shipment->id],
            ]);

        $response->assertRedirect(route('portal.logistics'));
        $response->assertSessionHas('success');

        // Verificar que el stock de la caja se dedujo en 1 unidad
        $this->assertEquals(99, $packageType->fresh()->stock_available);

        // Verificar asiento inmutable en el Kardex de empaque
        $this->assertDatabaseHas('packaging_movements', [
            'package_type_id' => $packageType->id,
            'shipment_id' => $shipment->id,
            'type' => 'CONSUMO_EMPAQUE_ENVIO',
            'quantity' => -1,
            'stock_before' => 100,
            'stock_after' => 99,
        ]);

        // Verificar que el envio quedo marcado con la caja y timestamp
        $shipment->refresh();
        $this->assertEquals($packageType->id, $shipment->package_type_id);
        $this->assertNotNull($shipment->packaging_fulfilled_at);
        $this->assertEquals('in_transit', $shipment->status);
    }

    public function test_packaging_fulfillment_service_triggers_rop_alert_when_stock_hits_threshold(): void
    {
        $packageType = PackageType::create([
            'code' => 'BOX-M02',
            'name' => 'Caja Mediana E-commerce',
            'type' => 'box',
            'length_cm' => 28,
            'width_cm' => 20,
            'height_cm' => 10,
            'max_weight_kg' => 4.0,
            'unit_cost' => 16.00,
            'stock_available' => 41,
            'reorder_point' => 40,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-ROP-ALERT-002',
            'status' => 'processing',
            'total' => 899.00,
            'shipping_address' => ['city' => 'Monterrey', 'state' => 'NL', 'zip' => '64000', 'name' => 'Cliente ROP'],
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'EST-ROP-2002',
            'courier' => 'estafeta',
            'status' => 'label_created',
            'weight_actual_kg' => 0.80,
            'weight_volumetric_kg' => 0.55,
            'shipping_cost_declared' => 160.00,
        ]);

        $service = new PackagingFulfillmentService();
        $movement = $service->fulfillShipmentPackaging($shipment, $packageType, $this->portalUser());

        $this->assertNotNull($movement);
        $this->assertEquals(40, $packageType->fresh()->stock_available);

        // Se debio disparar una alerta ROP en operational_tasks para compras
        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'compras',
            'title' => "Alerta ROP: Stock critico de caja {$packageType->code}",
            'priority' => 'urgente',
            'status' => 'pending',
        ]);
    }

    public function test_individual_shipment_packaging_can_be_fulfilled_in_one_click(): void
    {
        $packageType = PackageType::create([
            'code' => 'POLY-S01',
            'name' => 'Bolsa Polymailer Segura',
            'type' => 'polymailer',
            'length_cm' => 25,
            'width_cm' => 35,
            'height_cm' => 2,
            'max_weight_kg' => 1.5,
            'unit_cost' => 4.50,
            'stock_available' => 500,
            'reorder_point' => 50,
        ]);

        $shipment = Shipment::create([
            'tracking_number' => '99M-ONECLICK-3003',
            'courier' => '99minutos',
            'status' => 'label_created',
            'weight_actual_kg' => 0.20,
            'weight_volumetric_kg' => 0.15,
            'shipping_cost_declared' => 95.00,
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.logistics.shipments.fulfill_packaging', $shipment->id), [
                'package_type_id' => $packageType->id,
            ]);

        $response->assertRedirect(route('portal.logistics'));
        $response->assertSessionHas('success');

        $this->assertEquals(499, $packageType->fresh()->stock_available);
        $this->assertNotNull($shipment->fresh()->packaging_fulfilled_at);
        $this->assertEquals('packed', $shipment->fresh()->status);
    }

    public function test_packaging_fulfillment_is_idempotent_and_does_not_double_deduct(): void
    {
        $packageType = PackageType::create([
            'code' => 'BOX-G03',
            'name' => 'Caja Grande Multiverso',
            'type' => 'box',
            'length_cm' => 35,
            'width_cm' => 25,
            'height_cm' => 15,
            'max_weight_kg' => 8.0,
            'unit_cost' => 24.50,
            'stock_available' => 80,
            'reorder_point' => 20,
        ]);

        $shipment = Shipment::create([
            'tracking_number' => 'FDX-IDEMP-4004',
            'courier' => 'fedex',
            'status' => 'label_created',
            'weight_actual_kg' => 1.20,
            'weight_volumetric_kg' => 1.10,
            'shipping_cost_declared' => 210.00,
        ]);

        $service = new PackagingFulfillmentService();

        // Primer llamado: debe descontar 1 unidad
        $service->fulfillShipmentPackaging($shipment, $packageType, $this->portalUser());
        $this->assertEquals(79, $packageType->fresh()->stock_available);

        // Segundo llamado: no debe descontar nuevamente
        $service->fulfillShipmentPackaging($shipment, $packageType, $this->portalUser());
        $this->assertEquals(79, $packageType->fresh()->stock_available);
        $this->assertEquals(1, PackagingMovement::where('shipment_id', $shipment->id)->count());
    }

    public function test_manual_stock_adjustment_creates_kardex_entry_and_resolves_rop_alert(): void
    {
        $packageType = PackageType::create([
            'code' => 'BOX-RESTOCK-05',
            'name' => 'Caja Restock Test',
            'type' => 'box',
            'length_cm' => 20,
            'width_cm' => 20,
            'height_cm' => 10,
            'unit_cost' => 15.00,
            'stock_available' => 25,
            'reorder_point' => 40,
        ]);

        // Simular alerta ROP previa
        OperationalTask::create([
            'category' => 'compras',
            'title' => "Alerta ROP: Stock critico de caja {$packageType->code}",
            'description' => 'Pendiente de reabastecimiento.',
            'priority' => 'urgente',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.logistics.packages.stock', $packageType->id), [
                'stock_available' => 150,
                'unit_cost' => 14.50,
                'reorder_point' => 40,
            ]);

        $response->assertRedirect(route('portal.logistics'));
        $response->assertSessionHas('success');

        $this->assertEquals(150, $packageType->fresh()->stock_available);

        // Verificar asiento de entrada en kardex de empaque (+125)
        $this->assertDatabaseHas('packaging_movements', [
            'package_type_id' => $packageType->id,
            'type' => 'ENTRADA_REABASTECIMIENTO',
            'quantity' => 125,
            'stock_before' => 25,
            'stock_after' => 150,
        ]);

        // Verificar que la alerta ROP quedo completada
        $this->assertDatabaseHas('operational_tasks', [
            'title' => "Alerta ROP: Stock critico de caja {$packageType->code}",
            'status' => 'completed',
        ]);
    }
}
