<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\ThreeWayMatching;
use App\Models\User;
use App\Models\VendorBill;
use App\Services\InboundReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Hub5FinanceAntiFraudAndCppTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'finanzas.lead@mitienda.online',
        ]);
    }

    public function test_pay_vendor_bill_rejects_when_spei_blocked_by_three_way_matching_discrepancy(): void
    {
        $bill = VendorBill::create([
            'bill_number' => 'BILL-DISCREPANT-01',
            'vendor_type' => 'apparel_workshop',
            'vendor_name' => 'Taller Textil Discrepante',
            'amount' => 12500.00,
            'status' => 'pending',
            'bank_clabe' => '012180001234567890',
            'bank_name' => 'BBVA México',
            'is_spei_blocked' => true,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->post(route('portal.finance.vendor_bills.pay', $bill->id));

        $response->assertRedirect(route('portal.finance', ['tab' => 'expenses']));
        $response->assertSessionHas('error');

        $bill->refresh();
        $this->assertEquals('pending', $bill->status);
        $this->assertNull($bill->paid_at);
        $this->assertNull($bill->payment_reference);
    }

    public function test_pay_vendor_bill_rejects_when_clabe_is_not_18_digits(): void
    {
        $bill = VendorBill::create([
            'bill_number' => 'BILL-BAD-CLABE-01',
            'vendor_type' => 'apparel_workshop',
            'vendor_name' => 'Taller Sin CLABE',
            'amount' => 4500.00,
            'status' => 'pending',
            'bank_clabe' => '12345678', // Invalid 8 digits
            'bank_name' => 'Santander',
            'is_spei_blocked' => false,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->post(route('portal.finance.vendor_bills.pay', $bill->id));

        $response->assertRedirect(route('portal.finance', ['tab' => 'expenses']));
        $response->assertSessionHas('error');

        $bill->refresh();
        $this->assertEquals('pending', $bill->status);
        $this->assertNull($bill->paid_at);
    }

    public function test_pay_vendor_bill_succeeds_when_not_blocked_and_clabe_is_valid(): void
    {
        $bill = VendorBill::create([
            'bill_number' => 'BILL-VALID-01',
            'vendor_type' => 'apparel_workshop',
            'vendor_name' => 'Taller Textil Confiable',
            'amount' => 8900.00,
            'status' => 'scheduled',
            'bank_clabe' => '012180009876543210', // Exact 18 digits
            'bank_name' => 'BBVA México',
            'is_spei_blocked' => false,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->post(route('portal.finance.vendor_bills.pay', $bill->id));

        $response->assertRedirect(route('portal.finance', ['tab' => 'expenses']));
        $response->assertSessionHas('success');

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertNotNull($bill->paid_at);
        $this->assertStringStartsWith('SPEI-BBVA-', $bill->payment_reference);
    }

    public function test_inbound_receipt_preserves_cpp_when_receiving_zero_cost_bonus_samples(): void
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor de Telas S.A.',
            'contact_email' => 'telas@proveedor.test',
        ]);

        $product = Product::create([
            'sku' => 'TEST-CPP-01',
            'slug' => 'test-cpp-01-slug',
            'title' => 'Chamarra de Piel Edición Especial',
            'price' => 1200.00,
            'cost' => 450.00,
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-CPP-01-L',
            'price' => 1200.00,
            'cost' => 450.00,
            'attributes' => ['size' => 'L'],
        ]);

        Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-SAMPLE-2026-01',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'total_amount' => 0.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity_ordered' => 5,
            'quantity_received' => 0,
            'unit_cost' => 0.00, // Zero cost samples / bonification
        ]);

        $inboundService = app(InboundReceiptService::class);
        $result = $inboundService->receivePurchaseOrder(
            purchaseOrder: $po,
            itemsReceived: [$poItem->id => 5],
            userId: $this->financeUser->id,
            notes: 'Recepción muestra cortesía'
        );

        $variant->refresh();
        $product->refresh();

        // Debe mantener el costo contable previo de $450.00 y NO diluir a $300 ni a $0.00
        $this->assertEquals(450.00, (float) $variant->cost);
        $this->assertEquals(450.00, (float) $product->cost);

        $inv = Inventory::where('variant_id', $variant->id)->where('location', 'warehouse-1')->first();
        $this->assertEquals(15, $inv->quantity); // 10 previos + 5 recibidos
    }

    public function test_inbound_receipt_assigns_default_cogs_percentage_if_brand_new_item_receives_zero_cost_sample(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fabricante Vanguardia',
            'contact_email' => 'vanguardia@test.com',
        ]);

        $product = Product::create([
            'sku' => 'TEST-CPP-NEW-01',
            'slug' => 'test-cpp-new-01',
            'title' => 'Top Deportivo Neón',
            'price' => 600.00,
            'cost' => null, // No previous cost
            'type' => 'dropship',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-CPP-NEW-01-M',
            'price' => 600.00,
            'cost' => null, // No previous cost
            'attributes' => ['size' => 'M'],
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-NEW-SAMPLE-01',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'total_amount' => 0.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity_ordered' => 2,
            'quantity_received' => 0,
            'unit_cost' => 0.00,
        ]);

        $inboundService = app(InboundReceiptService::class);
        $inboundService->receivePurchaseOrder(
            purchaseOrder: $po,
            itemsReceived: [$poItem->id => 2],
            userId: $this->financeUser->id,
            notes: 'Recepción muestra item nuevo'
        );

        $variant->refresh();
        $product->refresh();

        // 600.00 * 0.40 = 240.00
        $this->assertEquals(240.00, (float) $variant->cost);
        $this->assertEquals(240.00, (float) $product->cost);
        $this->assertEquals('both', $product->type); // Auto transicion dropship -> both
    }
}
