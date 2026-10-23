<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\AllyCouponDirB;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PwaPushNotificationLog;
use App\Models\PwaPushSubscription;
use App\Models\StockLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontPwaModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function createProductWithStock(string $sku, int $quantity = 10, float $price = 450.00, float $cost = 120.00): array
    {
        $product = Product::create([
            'title' => 'Brief Torzo Anatomico ' . $sku,
            'slug' => 'brief-torzo-' . strtolower($sku),
            'sku' => $sku,
            'price' => $price,
            'cost' => $cost,
            'visible' => true,
            'brand' => 'Torzo',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku . '-M',
            'attributes' => ['size' => 'M', 'color' => 'Negro'],
            'price' => $price,
            'cost' => $cost,
            'visible' => true,
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => $quantity,
            'reserved' => 0,
        ]);

        return [$product, $variant, $inventory];
    }

    public function test_multiverse_catalog_renders_products_from_real_database(): void
    {
        [$product] = $this->createProductWithStock('TRZ-CAT-01', 5, 399.00);

        // Test de API de catalogo
        $portalUser = \App\Models\User::factory()->create(['role' => 'portal']);
        $apiResponse = $this->actingAs($portalUser)->getJson('/api/products');
        $apiResponse->assertStatus(200)
            ->assertJsonFragment([
                'title' => $product->title,
                'sku' => 'TRZ-CAT-01',
            ]);

        // Test de vista Storefront PWA principal
        $viewResponse = $this->get('/');
        $viewResponse->assertStatus(200)
            ->assertSee('store-body')
            ->assertSee('partnerWelcomeBanner');
    }

    public function test_pdp_endpoint_returns_anatomical_matrix_and_textile_composition(): void
    {
        [$product, $variant] = $this->createProductWithStock('TRZ-PDP-01', 8, 420.00);

        $response = $this->getJson('/api/v1/store/products/' . $product->slug);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sku' => 'TRZ-PDP-01',
                    'brand' => 'Torzo',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'variants',
                    'sizes',
                ],
            ]);
    }

    public function test_stock_locks_lifecycle_reserve_block_and_release(): void
    {
        [$product, $variant] = $this->createProductWithStock('TRZ-LCK-01', 2, 450.00);
        $cartSessionId = 'sess_module_lock_test';

        // 1. Agregar y reservar 2 unidades (stock maximo fisico)
        $res1 = $this->withHeader('X-Cart-Session-Id', $cartSessionId)
            ->postJson('/api/v1/store/cart/items', [
                'cart_id' => $cartSessionId,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'size' => 'M',
                'quantity' => 2,
            ]);

        $res1->assertStatus(200);
        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => $cartSessionId,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'status' => 'active',
        ]);

        // 2. Otro comprador agrega prenda: al no haber stock fisico libre, pasa a Linea B (On-Demand) sin candado
        $res2 = $this->withHeader('X-Cart-Session-Id', 'sess_other_buyer')
            ->postJson('/api/v1/store/cart/items', [
                'cart_id' => 'sess_other_buyer',
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'size' => 'M',
                'quantity' => 1,
            ]);

        $res2->assertStatus(200)
            ->assertJsonPath('items.0.line_type', 'line_b')
            ->assertJsonPath('items.0.has_active_lock', false);

        $this->assertDatabaseMissing('stock_locks', [
            'cart_id' => 'sess_other_buyer',
            'status' => 'active',
        ]);

        // 3. Liberar carrito y comprobar liberacion de candados
        $resRelease = $this->withHeader('X-Cart-Session-Id', $cartSessionId)
            ->postJson('/api/v1/store/cart/release', ['cart_id' => $cartSessionId]);

        $resRelease->assertStatus(200);
        $this->assertDatabaseMissing('stock_locks', [
            'cart_id' => $cartSessionId,
            'status' => 'active',
        ]);
    }

    public function test_b2b_ally_coupon_validation_and_discount_calculation(): void
    {
        $ally = Ally::create([
            'code' => 'PARADISO',
            'name' => 'Club Paradiso B2B',
            'category' => 'circuit_party',
            'portal_token' => 'tok_paradiso_' . Str::random(10),
            'contact_email' => 'convenios@paradiso.club',
            'status' => 'active',
        ]);

        AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'PARADISO15',
            'discount_pct' => 15.00,
            'utm_source' => 'paradiso',
            'utm_campaign' => 'verano_2026',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/store/coupons/validate', [
            'coupon_code' => 'PARADISO15',
            'subtotal' => 1000.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'code' => 'PARADISO15',
                'type' => 'ally_b2b',
                'discount_pct' => 15.0,
                'discount_amount' => 150.0,
                'ally_id' => $ally->id,
            ]);
    }

    public function test_e2e_checkout_creates_immutable_order_and_kardex_movement(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithStock('TRZ-E2E-01', 5, 500.00, 150.00);
        $cartSessionId = 'sess_e2e_checkout';

        // Agregar al carrito con candado
        $this->withHeader('X-Cart-Session-Id', $cartSessionId)
            ->postJson('/api/v1/store/cart/items', [
                'cart_id' => $cartSessionId,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'size' => 'M',
                'quantity' => 1,
            ]);

        // Ejecutar checkout
        $checkoutPayload = [
            'customer' => [
                'name' => 'Carlos Hernandez',
                'email' => 'carlos.h@ejemplo.com',
                'phone' => '5544332211',
            ],
            'shipping' => [
                'street' => 'Colima 240, Depto 102',
                'postal_code' => '06700',
                'colony' => 'Roma Norte',
                'city' => 'Cuauhtemoc',
                'state' => 'Ciudad de Mexico',
                'shipping_cost' => 149.00,
            ],
            'payment' => [
                'method' => 'spei_bbva',
            ],
        ];

        $response = $this->withHeader('X-Cart-Session-Id', $cartSessionId)
            ->postJson('/api/v1/store/checkout', $checkoutPayload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'order' => [
                    'status' => 'pending',
                    'payment_method' => 'spei_bbva',
                ],
            ]);

        $orderNumber = $response->json('order.order_number');
        $this->assertStringStartsWith('#ORD-2026-', $orderNumber);

        // Verificar descuento de inventario
        $this->assertEquals(4, $inventory->fresh()->quantity);

        // Verificar asiento inmutable en Kardex
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => -1,
        ]);

        $movement = InventoryMovement::where('inventory_id', $inventory->id)->first();
        $this->assertStringContainsString('SALIDA_VENTA_ONLINE', $movement->reason);

        // Verificar guía DHL Express en mesa de expedición
        $this->assertDatabaseHas('shipments', [
            'courier' => 'dhl_express',
            'status' => 'label_created',
        ]);
    }

    public function test_web_push_vapid_subscription_and_audited_log_delivery(): void
    {
        $payload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/module-test-endpoint-99',
            'keys' => [
                'p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA',
                'auth' => 'tBH26G9fVp',
            ],
            'topics' => [
                'stock_urgency' => true,
                'order_tracking' => true,
            ],
        ];

        // 1. Suscribirse
        $subResponse = $this->postJson('/api/v1/store/push/subscribe', $payload);
        $subResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'subscription' => [
                    'endpoint' => $payload['endpoint'],
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('pwa_push_subscriptions', [
            'endpoint' => $payload['endpoint'],
            'is_active' => true,
        ]);

        // Debe registrar log de bienvenida
        $this->assertDatabaseHas('pwa_push_notification_logs', [
            'notification_type' => 'welcome',
            'delivery_status' => 'sent',
        ]);

        // 2. Enviar push de prueba
        $testResponse = $this->postJson('/api/v1/store/push/test', [
            'endpoint' => $payload['endpoint'],
        ]);

        $testResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'log' => [
                    'type' => 'test',
                    'delivery_status' => 'sent',
                ],
            ]);

        // 3. Desuscribirse
        $unsubResponse = $this->postJson('/api/v1/store/push/unsubscribe', [
            'endpoint' => $payload['endpoint'],
        ]);

        $unsubResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('pwa_push_subscriptions', [
            'endpoint' => $payload['endpoint'],
            'is_active' => false,
        ]);
    }

    public function test_service_worker_and_manifest_files_are_accessible(): void
    {
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('manifest.json'));

        $manifestContent = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertIsArray($manifestContent);
        $this->assertEquals('standalone', $manifestContent['display'] ?? '');
    }

    public function test_strict_zero_emojis_across_all_storefront_assets(): void
    {
        $filesToCheck = [
            public_path('js/storefront/store-pwa.js'),
            public_path('css/store-pwa.css'),
            public_path('sw.js'),
            resource_path('views/layouts/store-pwa.blade.php'),
            resource_path('views/portal/store.blade.php'),
            app_path('Services/CustomerAccountService.php'),
            app_path('Services/WebPushNotificationService.php'),
            app_path('Http/Controllers/Api/StorefrontApiController.php'),
        ];

        $emojiRegex = '/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1FA00}-\x{1FAFF}]/u';

        foreach ($filesToCheck as $file) {
            $this->assertFileExists($file, "El archivo {$file} no existe.");
            $content = file_get_contents($file);
            $hasEmoji = preg_match($emojiRegex, $content, $matches);
            $this->assertFalse((bool) $hasEmoji, "Se encontro un emoji en {$file}: " . ($matches[0] ?? ''));
        }
    }

    public function test_storefront_renders_custom_footer_config(): void
    {
        \App\Models\CmsOfficialDocument::updateOrCreate(
            ['code' => 'STORE_FOOTER_CONFIG'],
            [
                'title' => 'Editor Oficial de Pie de Página (Footer de Tienda)',
                'payload' => [
                    'brand_summary' => 'Narrativa corporativa única de prueba footer',
                    'whatsapp_contact' => '+52 55 9988 7766',
                    'support_email' => 'concierge@multiverso.test',
                    'shipping_badge_text' => 'Flete express 24h garantizado',
                    'packaging_badge_text' => 'Caja discreta sin logotipos externos',
                    'payments_badge_text' => 'Procesamiento encriptado bancario',
                    'copyright_text' => '(C) 2026 Multiverso Test Corp.',
                    'show_revops_link' => true,
                    'show_zero_emojis_badge' => true,
                ],
                'version' => '1.0',
                'is_active' => true,
            ]
        );

        $response = $this->get('/');
        $response->assertStatus(200)
            ->assertSee('Narrativa corporativa única de prueba footer')
            ->assertSee('+52 55 9988 7766')
            ->assertSee('concierge@multiverso.test')
            ->assertSee('Flete express 24h garantizado')
            ->assertSee('(C) 2026 Multiverso Test Corp.')
            ->assertSee('Consola RevOps');
    }
}
