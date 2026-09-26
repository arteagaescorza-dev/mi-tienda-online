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
        Route::post('/purchases/orders/{id}/receive', [InventoryApiController::class, 'receivePurchaseOrder']);
        Route::post('/scraping/pending/{id}/curate', [InventoryApiController::class, 'curateScrapedItem']);
        Route::post('/cycle-counts', [InventoryApiController::class, 'recordCycleCount']);
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

    // 4. FINANZAS & CONCILIACIÓN (HUB 5)
    Route::prefix('finance')->group(function () {
        Route::post('/dunning/{id}/retry', [FinanceApiController::class, 'retryDunningCharge']);
        Route::post('/dunning/{id}/recover', [FinanceApiController::class, 'markDunningRecovered']);
        Route::get('/orders/{id}/margin', [FinanceApiController::class, 'getOrderMarginBreakdown']);
    });

    // 5. LOGÍSTICA & POST-VENTA (HUB 6)
    Route::prefix('logistics')->group(function () {
        Route::post('/manifests', [LogisticsApiController::class, 'generateManifest']);
        Route::post('/rma/{id}/inspect', [LogisticsApiController::class, 'inspectRma']);
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
        Route::post('/tasks/{id}/complete', [DashboardApiController::class, 'completeTask']);
        Route::post('/environment/switch', [DashboardApiController::class, 'switchEnvironment']);
        Route::post('/copilot/chat', [DashboardApiController::class, 'copilotChat']);
    });
});
