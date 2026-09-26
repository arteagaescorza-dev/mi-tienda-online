<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PackageType;
use App\Models\RmaRequest;
use App\Models\Shipment;
use App\Models\ShippingManifest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsModuleTest extends TestCase
{
    use RefreshDatabase;

    private function portalUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin.logistics@test.com'],
            [
                'name' => 'Admin Logistics',
                'password' => bcrypt('password'),
                'role' => 'portal',
            ]
        );
    }

    public function test_admin_can_view_logistics_hub_with_real_data(): void
    {
        $packageType = PackageType::firstOrCreate(
            ['code' => 'BOX-TEST-01'],
            [
                'name' => 'Caja Test Unitario',
                'type' => 'box',
                'length_cm' => 20,
                'width_cm' => 15,
                'height_cm' => 10,
                'stock_available' => 120,
                'unit_cost' => 15.00,
            ]
        );

        $order = Order::firstOrCreate(
            ['order_number' => 'ORD-LOG-TEST-001'],
            [
                'status' => 'processing',
                'total' => 599.00,
                'shipping_address' => ['city' => 'CDMX', 'state' => 'CDMX', 'zip' => '03100', 'name' => 'Cliente Test Logistics'],
            ]
        );

        $shipment = Shipment::firstOrCreate(
            ['tracking_number' => 'DHL-LOG-TEST-999'],
            [
                'order_id' => $order->id,
                'courier' => 'dhl_express',
                'status' => 'packed',
                'weight_actual_kg' => 0.85,
                'weight_volumetric_kg' => 0.60,
                'shipping_cost_declared' => 160.00,
            ]
        );

        $response = $this->actingAs($this->portalUser())->get(route('portal.logistics'));

        $response->assertStatus(200);
        $response->assertViewIs('portal.logistics');
        $response->assertSee('Mesa de Expedición');
        $response->assertSee('Torre de Control');
        $response->assertSee('Garantías');
        $response->assertSee('Cajas');
        $response->assertSee('Corte DHL: 14:00 hrs');
        $response->assertSee($shipment->tracking_number);
        $response->assertSee($packageType->code);
    }

    public function test_can_close_shipping_manifest(): void
    {
        $order = Order::firstOrCreate(
            ['order_number' => 'ORD-MANIFEST-TEST'],
            [
                'status' => 'processing',
                'total' => 450.00,
                'shipping_address' => ['city' => 'Guadalajara', 'state' => 'JAL', 'zip' => '44100', 'name' => 'Cliente Manifest'],
            ]
        );

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'EST-MANIFEST-' . uniqid(),
            'courier' => 'estafeta',
            'status' => 'packed',
            'weight_actual_kg' => 0.50,
            'weight_volumetric_kg' => 0.40,
            'shipping_cost_declared' => 120.00,
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.logistics.manifests.close'), [
                'courier' => 'estafeta',
                'driver_name' => 'Carlos Conductor Test',
                'driver_badge_or_truck' => 'GAF-TEST-88 / Placas 999-XYZ',
                'shipment_ids' => [$shipment->id],
            ]);

        $response->assertRedirect(route('portal.logistics'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shipping_manifests', [
            'courier' => 'estafeta',
            'driver_name' => 'Carlos Conductor Test',
            'status' => 'signed',
            'total_packages' => 1,
        ]);

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => 'in_transit',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    public function test_can_inspect_rma_with_sanitary_approval(): void
    {
        $rma = RmaRequest::create([
            'rma_number' => 'RMA-TEST-' . uniqid(),
            'customer_name' => 'Cliente Test Sanitario',
            'customer_phone' => '+52 55 1122 3344',
            'product_sku' => 'SKU-RMA-TEST',
            'product_name' => 'Bóxer Modal Test',
            'reason' => 'sizing_wrong',
            'sanitary_status' => 'pending_inspection',
            'status' => 'open',
            'refund_or_credit_amount' => 250.00,
        ]);

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.logistics.rma.inspect', $rma->id), [
                'inspector_name' => 'Inspector Sanitario Certificado',
                'has_hygiene_seal_intact' => '1',
                'has_factory_tag' => '1',
                'verdict' => 'approved',
                'technical_notes' => 'Precinto íntegro verificado en laboratorio.',
                'resolution_type' => 'replacement_shipped',
            ]);

        $response->assertRedirect(route('portal.logistics'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rma_requests', [
            'id' => $rma->id,
            'sanitary_status' => 'approved_replacement',
            'status' => 'resolved',
        ]);

        $this->assertDatabaseHas('rma_inspection_logs', [
            'rma_id' => $rma->id,
            'verdict' => 'approved',
            'has_hygiene_seal_intact' => 1,
        ]);
    }

    public function test_rma_is_rejected_if_hygiene_seal_is_broken(): void
    {
        $rma = RmaRequest::create([
            'rma_number' => 'RMA-VIOLATED-' . uniqid(),
            'customer_name' => 'Cliente Sello Roto',
            'product_sku' => 'SKU-VIOLATED',
            'product_name' => 'Tanga Desprecintada',
            'reason' => 'sizing_wrong',
            'sanitary_status' => 'pending_inspection',
            'status' => 'open',
        ]);

        // Attempting to approve even with broken seal must enforce rejection
        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.logistics.rma.inspect', $rma->id), [
                'inspector_name' => 'Inspector Sanitario',
                'has_hygiene_seal_intact' => null,
                'has_factory_tag' => null,
                'verdict' => 'approved', // should be forced to rejected
                'technical_notes' => 'Sello removido por cliente.',
            ]);

        $response->assertRedirect(route('portal.logistics'));

        $this->assertDatabaseHas('rma_requests', [
            'id' => $rma->id,
            'sanitary_status' => 'rejected_used_hygiene',
            'status' => 'rejected',
        ]);
    }

    public function test_can_update_package_type_stock(): void
    {
        $packageType = PackageType::firstOrCreate(
            ['code' => 'BOX-STOCK-TEST'],
            [
                'name' => 'Caja Stock Ajuste',
                'type' => 'box',
                'length_cm' => 25,
                'width_cm' => 18,
                'height_cm' => 8,
                'stock_available' => 50,
                'unit_cost' => 14.00,
            ]
        );

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.logistics.packages.stock', $packageType->id), [
                'stock_available' => 450,
                'unit_cost' => 14.00,
            ]);

        $response->assertRedirect(route('portal.logistics'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('package_types', [
            'id' => $packageType->id,
            'stock_available' => 450,
        ]);
    }
}
