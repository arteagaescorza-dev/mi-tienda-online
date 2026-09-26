<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\WarehouseSlot;
use App\Models\ApparelFabric;
use App\Models\ApparelColor;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductBundle;
use App\Models\BundleItem;
use App\Models\OperationalTask;
use App\Models\SupportCase;
use App\Models\Coupon;
use App\Models\CustomerSubscription;
use App\Models\DunningRetryLog;
use App\Models\OrderFinancialMargin;
use App\Models\PackageType;
use App\Models\ShippingManifest;
use App\Models\Shipment;
use App\Models\RmaRequest;
use App\Models\RmaInspectionLog;
use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyCouponDirB;
use App\Models\CustomRole;
use App\Models\CustomPermission;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Order;

class RevOpsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_procurement_models_persistence(): void
    {
        $supplier = Supplier::create([
            'name' => 'Torzo Mayorista',
            'code' => 'SUP-TORZO',
        ]);

        $product = Product::create([
            'title' => 'Jockstrap Deportivo Marte',
            'slug' => 'jockstrap-deportivo-marte',
            'sku' => 'MAR-JCK-001',
            'price' => 399.00,
        ]);

        $slot = WarehouseSlot::create([
            'slot_code' => 'SLOT-A01-N1',
            'zone' => 'ZONA_A',
            'aisle' => 'P01',
            'rack' => 'R01',
            'shelf' => 'N1',
            'is_occupied' => true,
            'current_product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('warehouse_slots', ['slot_code' => 'SLOT-A01-N1']);
        $this->assertEquals($product->id, $slot->product->id);

        $po = PurchaseOrder::create([
            'po_number' => 'OC-202609-001',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 60,
            'total_amount' => 7200.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'size_name' => 'M',
            'quantity_ordered' => 30,
            'unit_cost' => 120.00,
            'total_cost' => 3600.00,
        ]);

        $this->assertCount(1, $po->items);
        $this->assertEquals('OC-202609-001', $poItem->purchaseOrder->po_number);

        $bundle = ProductBundle::create([
            'name' => 'Pack Triple Jocks',
            'slug' => 'pack-triple-jocks',
            'bundle_type' => 'pack_multiple',
            'bundle_price' => 999.00,
        ]);

        BundleItem::create([
            'product_bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertCount(1, $bundle->items);
    }

    public function test_dashboard_tasks_and_rbac_persistence(): void
    {
        $task = OperationalTask::create([
            'title' => 'Revisar ROP de Boxers Clásicos',
            'category' => 'inventory_rop',
            'priority' => 'critical',
            'status' => 'pending',
            'sla_minutes_limit' => 60,
        ]);

        $this->assertDatabaseHas('operational_tasks', ['title' => 'Revisar ROP de Boxers Clásicos']);

        $role = CustomRole::create([
            'name' => 'operario_almacen',
            'label' => 'Operario de Almacén',
            'scope' => 'all_multiverse',
        ]);

        $permission = CustomPermission::create([
            'name' => 'inventory.inbound_receive',
            'label' => 'Recibir Mercancía Inbound',
            'group' => 'inventory',
        ]);

        $role->permissions()->attach($permission->id);

        $this->assertCount(1, $role->permissions);
        $this->assertCount(1, $permission->roles);
    }

    public function test_marketing_coupons_and_crm_persistence(): void
    {
        $coupon = Coupon::create([
            'code' => 'TESTPROMO20',
            'discount_type' => 'percent',
            'discount_value' => 20.00,
            'min_order_amount' => 500.00,
            'target_universe' => 'marte',
        ]);

        $this->assertDatabaseHas('coupons', ['code' => 'TESTPROMO20']);
        $this->assertTrue($coupon->is_active);
    }

    public function test_finance_subscriptions_and_dunning_persistence(): void
    {
        $sub = CustomerSubscription::create([
            'subscription_folio' => 'SUB-MARTE-001',
            'tier_name' => 'Caja Marte Nivel 2',
            'universe' => 'marte',
            'monthly_amount' => 799.00,
            'status' => 'active',
        ]);

        $dunning = DunningRetryLog::create([
            'subscription_id' => $sub->id,
            'attempt_number' => 1,
            'decline_code' => 'insufficient_funds',
            'action_taken' => 'whatsapp_link_sent',
            'was_recovered' => true,
            'amount_recovered' => 799.00,
        ]);

        $this->assertCount(1, $sub->dunningLogs);
        $this->assertTrue($dunning->was_recovered);
    }

    public function test_logistics_manifests_and_rma_persistence(): void
    {
        $pkg = PackageType::create([
            'code' => 'BOX-TEST',
            'name' => 'Caja Prueba',
            'type' => 'box',
            'length_cm' => 20,
            'width_cm' => 15,
            'height_cm' => 10,
            'unit_cost' => 14.00,
        ]);

        $manifest = ShippingManifest::create([
            'manifest_number' => 'MAN-DHL-20260926',
            'courier' => 'dhl_express',
            'total_packages' => 12,
        ]);

        $shipment = Shipment::create([
            'manifest_id' => $manifest->id,
            'tracking_number' => 'DHL-TRACK-998811',
            'courier' => 'dhl_express',
            'status' => 'in_transit',
        ]);

        $this->assertCount(1, $manifest->shipments);

        $rma = RmaRequest::create([
            'rma_number' => 'RMA-202609-01',
            'customer_name' => 'Esteban Gomez',
            'product_sku' => 'MAR-JCK-001',
            'product_name' => 'Jockstrap Deportivo',
            'reason' => 'sizing_wrong',
            'sanitary_status' => 'approved_store_credit',
            'status' => 'resolved',
        ]);

        $inspection = RmaInspectionLog::create([
            'rma_id' => $rma->id,
            'inspector_name' => 'Mariana Ruiz',
            'has_hygiene_seal_intact' => true,
            'has_factory_tag' => true,
            'verdict' => 'approved',
        ]);

        $this->assertCount(1, $rma->inspectionLogs);
        $this->assertTrue($inspection->has_hygiene_seal_intact);
    }

    public function test_allies_and_clearing_house_persistence(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-TEST-CLUB',
            'name' => 'Club Test CDMX',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'portal_token' => 'tok_test_club_2026',
            'commission_rate' => 12.00,
            'lead_acquisition_cost' => 20.00,
            'clearing_balance' => -1500.00,
        ]);

        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => '20% OFF en Cover Club Test',
            'slug' => 'cover-club-test',
            'universe' => 'marte',
            'benefit' => '20% descuento en cover',
            'display_format' => 'popup_modal',
        ]);

        $coupon = AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'TESTCLUB15',
            'discount_pct' => 15.00,
            'commission_rate' => 12.00,
            'universe' => 'marte',
            'utm_source' => 'test_club',
            'utm_campaign' => 'mayo_2026',
        ]);

        $this->assertCount(1, $ally->campaignsDirA);
        $this->assertCount(1, $ally->couponsDirB);
        $this->assertEquals($ally->id, $campaign->ally->id);
        $this->assertEquals($ally->id, $coupon->ally->id);
    }
}
