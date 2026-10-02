<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\RevOps\InventoryApiController;
use App\Http\Controllers\Api\RevOps\InventorySettingsApiController;
use App\Http\Controllers\Api\RevOps\ProductSkuAndDuplicateApiController;
use App\Http\Controllers\Api\RevOps\SupplierApiController;
use App\Http\Controllers\Api\RevOps\BrandApiController;
use App\Http\Controllers\Api\RevOps\StockReservationApiController;
use App\Http\Controllers\Api\RevOps\MarketingApiController;
use App\Http\Controllers\Api\RevOps\FinanceApiController;
use App\Http\Controllers\Api\RevOps\LogisticsApiController;
use App\Http\Controllers\Api\RevOps\AlliesApiController;
use App\Http\Controllers\Api\RevOps\DashboardApiController;
use App\Http\Controllers\Api\StorefrontApiController;
use App\Http\Controllers\PortalVendorController;

// =========================================================================
// STOREFRONT D2C PUBLIC API ENDPOINTS
// =========================================================================
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart/items', [CartController::class, 'addItem']);
Route::put('/cart/sync', [CartController::class, 'sync']);
Route::patch('/cart/items/{item}', [CartController::class, 'updateItem']);
Route::delete('/cart/items/{item}', [CartController::class, 'removeItem']);

// =========================================================================
// REVOPS V1 RESTFUL API ENDPOINTS (CONTRACT-DRIVEN)
// =========================================================================
Route::prefix('v1')->group(function () {
    // 1. INVENTARIO & COMPRAS (HUB 4)
    Route::prefix('inventory')->group(function () {
        Route::get('/purchases/credit-agreements', [InventoryApiController::class, 'getCreditAgreements']);
        Route::post('/purchases/credit-agreements', [InventoryApiController::class, 'storeCreditAgreement']);
        Route::get('/purchases/credit-agreements/{id}', [InventoryApiController::class, 'getCreditAgreementDetail']);
        Route::post('/purchases/credit-agreements/{id}/void', [InventoryApiController::class, 'voidCreditAgreement']);
        Route::get('/purchases/orders', [InventoryApiController::class, 'getPurchaseOrders']);
        Route::get('/purchases/orders/{id}', [InventoryApiController::class, 'getPurchaseOrderDetail']);
        Route::get('/purchases/kardex', [InventoryApiController::class, 'getPurchaseKardex']);
        Route::post('/purchases/orders', [InventoryApiController::class, 'storePurchaseOrder']);
        Route::post('/purchases/orders/{id}/dispatch', [InventoryApiController::class, 'dispatchPurchaseOrder']);
        Route::post('/purchases/orders/{id}/receive', [InventoryApiController::class, 'receivePurchaseOrder']);
        Route::post('/scraping/pending/{id}/curate', [InventoryApiController::class, 'curateScrapedItem']);
        Route::post('/cycle-counts', [InventoryApiController::class, 'recordCycleCount']);
        Route::get('/bundles', [InventoryApiController::class, 'getBundles']);
        Route::post('/bundles', [InventoryApiController::class, 'storeBundle']);
        Route::post('/bundles/audit-deficit', [InventoryApiController::class, 'auditBomDeficit']);
        Route::post('/adjust', [InventoryApiController::class, 'adjustStock']);

        // GESTION FULLSTACK DE PROVEEDORES & EXTRANET HUB 7 (TASK-PUR-02 / SLICE 4)
        Route::prefix('suppliers')->group(function () {
            Route::get('/', [SupplierApiController::class, 'index']);
            Route::post('/', [SupplierApiController::class, 'store']);
            Route::get('/{id}', [SupplierApiController::class, 'show']);
            Route::put('/{id}', [SupplierApiController::class, 'update']);
            Route::delete('/{id}', [SupplierApiController::class, 'destroy']);
            Route::post('/{id}/toggle-status', [SupplierApiController::class, 'toggleStatus']);
            Route::post('/{id}/invite-extranet', [SupplierApiController::class, 'inviteExtranet']);
            Route::post('/{id}/brands', [SupplierApiController::class, 'addBrand']);
            Route::delete('/{id}/brands', [SupplierApiController::class, 'removeBrand']);
        });

        // ALTA DE PRODUCTO DESDE MODAL PANORAMICO (TASK-INV-20 / SLICE 6)
        Route::post('/products', [ProductSkuAndDuplicateApiController::class, 'storeProduct']);

        // MOTOR INTELIGENTE DE AUTO-SKU, DETECCION DE DUPLICADOS Y COPILOTO IA (TASK-INV-15 / SLICE 2 & 3)
        Route::post('/products/generate-sku', [ProductSkuAndDuplicateApiController::class, 'generateSku']);
        Route::post('/products/check-duplicate', [ProductSkuAndDuplicateApiController::class, 'checkDuplicate']);
        Route::post('/products/generate-description', [ProductSkuAndDuplicateApiController::class, 'generateDescription']);

        // MODAL PANORAMICO CENTRADO DE DETALLE Y ACTUALIZACION INTEGRAL DE SKU (TASK-INV-18 / SLICE 4)
        Route::get('/products/{sku}/detail', [ProductSkuAndDuplicateApiController::class, 'getSkuDetail']);
        Route::put('/products/{sku}/update-complete', [ProductSkuAndDuplicateApiController::class, 'updateComplete']);

        // GESTOR DE AJUSTES DE INVENTARIO: TALLAS, VOLUMENES Y CATEGORIAS (TASK-INV-14)
        Route::prefix('settings')->group(function () {
            Route::get('/size-curves', [InventorySettingsApiController::class, 'getSizeCurves']);
            Route::post('/size-curves', [InventorySettingsApiController::class, 'storeSizeCurve']);
            Route::put('/size-curves/{id}', [InventorySettingsApiController::class, 'updateSizeCurve']);
            Route::patch('/size-curves/{id}/toggle-active', [InventorySettingsApiController::class, 'toggleSizeCurveActive']);
            Route::post('/size-curves/{id}/archive', [InventorySettingsApiController::class, 'archiveSizeCurve']);
            Route::post('/size-curves/{id}/restore', [InventorySettingsApiController::class, 'restoreSizeCurve']);
            Route::delete('/size-curves/{id}', [InventorySettingsApiController::class, 'destroySizeCurve']);

            Route::post('/size-curves/{curveId}/sizes', [InventorySettingsApiController::class, 'storeSize']);
            Route::put('/sizes/{sizeId}', [InventorySettingsApiController::class, 'updateSize']);
            Route::patch('/sizes/{sizeId}/toggle-active', [InventorySettingsApiController::class, 'toggleSizeActive']);
            Route::delete('/sizes/{sizeId}', [InventorySettingsApiController::class, 'destroySize']);

            Route::get('/fragrance-volumes', [InventorySettingsApiController::class, 'getFragranceVolumes']);
            Route::post('/fragrance-volumes', [InventorySettingsApiController::class, 'storeFragranceVolume']);
            Route::put('/fragrance-volumes/{id}', [InventorySettingsApiController::class, 'updateFragranceVolume']);
            Route::patch('/fragrance-volumes/{id}/toggle-active', [InventorySettingsApiController::class, 'toggleFragranceVolumeActive']);
            Route::post('/fragrance-volumes/{id}/archive', [InventorySettingsApiController::class, 'archiveFragranceVolume']);
            Route::post('/fragrance-volumes/{id}/restore', [InventorySettingsApiController::class, 'restoreFragranceVolume']);
            Route::delete('/fragrance-volumes/{id}', [InventorySettingsApiController::class, 'destroyFragranceVolume']);

            Route::get('/categories', [InventorySettingsApiController::class, 'getCategories']);
            Route::post('/categories', [InventorySettingsApiController::class, 'storeCategory']);
            Route::put('/categories/{id}', [InventorySettingsApiController::class, 'updateCategory']);
            Route::patch('/categories/{id}/toggle-active', [InventorySettingsApiController::class, 'toggleCategoryActive']);
            Route::post('/categories/{id}/archive', [InventorySettingsApiController::class, 'archiveCategory']);
            Route::post('/categories/{id}/restore', [InventorySettingsApiController::class, 'restoreCategory']);
            Route::delete('/categories/{id}', [InventorySettingsApiController::class, 'destroyCategory']);

            // GESTIÓN INTEGRAL DE MARCAS COMERCIALES (TASK-BRD-01)
            Route::get('/brands', [BrandApiController::class, 'index']);
            Route::post('/brands', [BrandApiController::class, 'store']);
            Route::get('/brands/{id}', [BrandApiController::class, 'show']);
            Route::put('/brands/{id}', [BrandApiController::class, 'update']);
            Route::patch('/brands/{id}/toggle-active', [BrandApiController::class, 'toggleActive']);
            Route::post('/brands/{id}/archive', [BrandApiController::class, 'archive']);
            Route::post('/brands/{id}/restore', [BrandApiController::class, 'restore']);
            Route::delete('/brands/{id}', [BrandApiController::class, 'destroy']);
            Route::post('/brands/{id}/assign-supplier', [BrandApiController::class, 'assignSupplier']);
        });

        // RUTA DIRECTA DE MARCAS EN INVENTARIO
        Route::prefix('brands')->group(function () {
            Route::get('/', [BrandApiController::class, 'index']);
            Route::post('/', [BrandApiController::class, 'store']);
            Route::get('/{id}', [BrandApiController::class, 'show']);
            Route::put('/{id}', [BrandApiController::class, 'update']);
            Route::patch('/{id}/toggle-active', [BrandApiController::class, 'toggleActive']);
            Route::post('/{id}/archive', [BrandApiController::class, 'archive']);
            Route::post('/{id}/restore', [BrandApiController::class, 'restore']);
            Route::delete('/{id}', [BrandApiController::class, 'destroy']);
            Route::post('/{id}/assign-supplier', [BrandApiController::class, 'assignSupplier']);
        });
    });

    // 2. CHECKOUT & RESERVA TEMPORAL DE STOCK (TASK-INT-06)
    Route::prefix('checkout')->group(function () {
        Route::post('/reserve-stock', [StockReservationApiController::class, 'reserve']);
        Route::post('/release-stock', [StockReservationApiController::class, 'release']);
        Route::post('/cleanup-expired-locks', [StockReservationApiController::class, 'cleanupExpired']);
    });

    // 3. MARKETING & CRM (HUB 3)
    Route::prefix('marketing')->group(function () {
        Route::post('/coupons/validate', [MarketingApiController::class, 'validateCoupon']);
        Route::post('/campaigns/{id}/toggle', [MarketingApiController::class, 'toggleCampaign']);
        Route::post('/lead-magnet/capture', [MarketingApiController::class, 'captureLead']);
        Route::post('/chatbot/message', [MarketingApiController::class, 'sendChatbotMessage']);
    });

    // 4. FINANZAS & CONCILIACIÓN (HUB 5) - PROTEGIDO CON PRIVACIDAD FINANCIERA (TASK-RBAC-03)
    Route::prefix('finance')->middleware('financial.privacy')->group(function () {
        Route::post('/dunning/{id}/retry', [FinanceApiController::class, 'retryDunningCharge']);
        Route::post('/dunning/{id}/recover', [FinanceApiController::class, 'markDunningRecovered']);
        Route::get('/orders/{id}/margin', [FinanceApiController::class, 'getOrderMarginBreakdown']);
        Route::post('/shipping-reconciliations/upload-report', [FinanceApiController::class, 'uploadShippingReconciliationReport']);
        Route::post('/shipping-audit/reconcile', [FinanceApiController::class, 'reconcileShippingAudit']);
        Route::post('/shipping-reconciliations/{id}/file-claim', [FinanceApiController::class, 'fileShippingDiscrepancyClaim']);

        // 3-Way Matching Inbound & Retenciones SAT (TASK-INT-07 / TASK-FIN-02)
        Route::get('/inbound-matching', [FinanceApiController::class, 'getInboundMatchings']);
        Route::post('/three-way-matching/verify', [FinanceApiController::class, 'verifyThreeWayMatching']);
        Route::post('/three-way-matching/{id}/debit-note', [FinanceApiController::class, 'issueDebitNote']);
        Route::post('/three-way-matching/{id}/authorize-spei', [FinanceApiController::class, 'authorizeSpeiPayout']);
        Route::get('/taxes/summary', [FinanceApiController::class, 'getTaxSummary']);
    });

    // 4B. GOBERNANZA, PRIVACIDAD RBAC & AUDITORIA MASTER (TASK-RBAC-02, TASK-RBAC-03, TASK-DSH-09, TASK-UX-03)
    Route::prefix('governance')->group(function () {
        Route::post('/roles/switch', [\App\Http\Controllers\PortalAccessController::class, 'switchRole']);
        Route::get('/audit-logs', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'getAuditLogs']);
        Route::post('/audit-logs/custom', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'recordCustomAction']);
        Route::get('/notifications', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'getNotifications']);
        Route::post('/notifications/dispatch', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'dispatchNotification']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'markAllNotificationsRead']);
        Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'markNotificationRead']);

        // Mensajeria Interdepartamental Operativa (TASK-COM-01)
        Route::get('/chat/messages', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'getChatMessages']);
        Route::post('/chat/messages', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'sendChatMessage']);
        Route::post('/chat/messages/{id}/resolve', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'resolveChatMessage']);
        Route::get('/chat/summary', [\App\Http\Controllers\Api\RevOps\GovernanceApiController::class, 'getChatSummary']);
    });

    // 5. LOGÍSTICA & POST-VENTA (HUB 6)
    Route::prefix('logistics')->group(function () {
        Route::post('/manifests', [LogisticsApiController::class, 'generateManifest']);
        Route::post('/rma/{id}/inspect', [LogisticsApiController::class, 'inspectRma']);
        Route::post('/rma/{id}/resolve', [LogisticsApiController::class, 'resolveRma']);
        Route::post('/rma/{id}/replacement-order', [LogisticsApiController::class, 'issueReplacementOrder']);
        Route::post('/rma/{id}/store-credit', [LogisticsApiController::class, 'issueStoreCredit']);
    });

    // 6. CONVENIOS & ALIADOS (HUB 7)
    Route::prefix('allies')->group(function () {
        Route::get('/{id}/clearing-balance', [AlliesApiController::class, 'getClearingBalance']);
        Route::post('/{id}/settle', [AlliesApiController::class, 'executeSettlement']);
        Route::post('/validate-voucher', [AlliesApiController::class, 'validateVoucher']);
        Route::get('/{id}/export-csv', [AlliesApiController::class, 'exportDoorListCsv']);
    });

    // 6.1. EXTRANET PROVEEDORES & FABRICANTES (HUB 7 - TASK-HUB-VND / TASK-INT-20)
    Route::prefix('hub7/vendor')->group(function () {
        Route::get('/orders', [PortalVendorController::class, 'getOrders']);
        Route::get('/orders/{id}', [PortalVendorController::class, 'getOrderDetail']);
        Route::post('/orders/{id}/confirm', [PortalVendorController::class, 'confirmOrder']);
        Route::post('/orders/{id}/dispatch', [PortalVendorController::class, 'dispatchOrder']);
        Route::get('/orders/{id}/shipment', [PortalVendorController::class, 'getShipment']);
        Route::get('/credit-agreements', [PortalVendorController::class, 'getCreditAgreements']);
    });

    // 7. DASHBOARD GENERAL, TAREAS & COPILOTO (HUB 1)
    Route::prefix('dashboard')->group(function () {
        Route::get('/tasks', [DashboardApiController::class, 'getOperationalTasks']);
        Route::post('/tasks/{id}/complete', [DashboardApiController::class, 'completeTask']);
        Route::get('/shift-logs', [DashboardApiController::class, 'getShiftLogs']);
        Route::get('/sales/incidents', [DashboardApiController::class, 'getSalesIncidents']);
        Route::post('/sales/rescue-whatsapp', [DashboardApiController::class, 'rescueAbandonedCartViaWhatsApp']);
        Route::post('/sales/validate-address', [DashboardApiController::class, 'validateOrderAddress']);
        Route::get('/sales/sepomex-lookup', [DashboardApiController::class, 'lookupSepomex']);
        Route::get('/analytics/charts', [DashboardApiController::class, 'getAnalyticsCharts']);
        Route::get('/system-health', [DashboardApiController::class, 'getSystemHealth']);
        Route::post('/system-health/ping', [DashboardApiController::class, 'pingSystemHealth']);
        Route::post('/environment/switch', [DashboardApiController::class, 'switchEnvironment']);
        Route::post('/copilot/chat', [DashboardApiController::class, 'copilotChat']);
    });

    // 8. STOREFRONT D2C CATALOG, PDP & CARRITO CON RESERVA TEMPORAL (PRIORIDAD 1 - SLICE 1.2 & 1.3)
    Route::prefix('store')->group(function () {
        Route::get('/products/{slug}', [StorefrontApiController::class, 'productDetail']);

        // Carrito Deslizable y Candados de Stock (TASK-INT-06)
        Route::get('/cart', [StorefrontApiController::class, 'getCart']);
        Route::post('/cart/items', [StorefrontApiController::class, 'addToCart']);
        Route::patch('/cart/items/{id}', [StorefrontApiController::class, 'updateCartItem']);
        Route::delete('/cart/items/{id}', [StorefrontApiController::class, 'removeCartItem']);
        Route::post('/cart/clear', [StorefrontApiController::class, 'clearCart']);
        Route::post('/cart/lock', [StorefrontApiController::class, 'lockCart']);
        Route::post('/cart/release', [StorefrontApiController::class, 'releaseCartLock']);

        // Checkout Transaccional, Cupones y Sepomex (Slice 1.4 / TASK-STR-03 / TASK-FIN-01)
        Route::post('/postal-code/lookup', [StorefrontApiController::class, 'validatePostalCode']);
        Route::post('/coupons/validate', [StorefrontApiController::class, 'validateCoupon']);
        Route::post('/checkout', [StorefrontApiController::class, 'checkout']);

        // Portal del Comprador, Tracking Courier y Suscripciones MRR (Slice 1.5 / TASK-STR-04 / TASK-STR-05)
        Route::get('/account/orders', [StorefrontApiController::class, 'orders']);
        Route::get('/account/tracking/{folio}', [StorefrontApiController::class, 'tracking']);
        Route::get('/account/subscriptions', [StorefrontApiController::class, 'subscriptions']);
        Route::post('/account/subscriptions/{id}/pause', [StorefrontApiController::class, 'pauseSubscription']);
        Route::post('/account/subscriptions/{id}/resume', [StorefrontApiController::class, 'resumeSubscription']);
        Route::post('/account/subscriptions/subscribe', [StorefrontApiController::class, 'subscribe']);
        Route::post('/account/profile', [StorefrontApiController::class, 'updateProfile']);

        // Motor de Notificaciones Web Push PWA (Slice 1.6 / TASK-STR-06)
        Route::post('/push/subscribe', [StorefrontApiController::class, 'pushSubscribe']);
        Route::post('/push/unsubscribe', [StorefrontApiController::class, 'pushUnsubscribe']);
        Route::post('/push/test', [StorefrontApiController::class, 'pushTest']);
    });
});
