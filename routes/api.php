<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\RevOps\InventoryApiController;
use App\Http\Controllers\Api\RevOps\StockReservationApiController;
use App\Http\Controllers\Api\RevOps\MarketingApiController;
use App\Http\Controllers\Api\RevOps\FinanceApiController;
use App\Http\Controllers\Api\RevOps\LogisticsApiController;
use App\Http\Controllers\Api\RevOps\AlliesApiController;
use App\Http\Controllers\Api\RevOps\DashboardApiController;
use App\Http\Controllers\Api\StorefrontApiController;

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
        Route::post('/purchases/orders', [InventoryApiController::class, 'storePurchaseOrder']);
        Route::post('/purchases/orders/{id}/dispatch', [InventoryApiController::class, 'dispatchPurchaseOrder']);
        Route::post('/purchases/orders/{id}/receive', [InventoryApiController::class, 'receivePurchaseOrder']);
        Route::post('/scraping/pending/{id}/curate', [InventoryApiController::class, 'curateScrapedItem']);
        Route::post('/cycle-counts', [InventoryApiController::class, 'recordCycleCount']);
        Route::get('/bundles', [InventoryApiController::class, 'getBundles']);
        Route::post('/bundles', [InventoryApiController::class, 'storeBundle']);
        Route::post('/bundles/audit-deficit', [InventoryApiController::class, 'auditBomDeficit']);
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
