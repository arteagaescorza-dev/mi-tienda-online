<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalAccessController;
use App\Http\Controllers\AuthModalController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\SupplierSyncController;
use App\Http\Controllers\SupplierColorMappingController;
use App\Http\Controllers\StorefrontController;

Route::get('/', [StorefrontController::class, 'home'])->name('store.home');
Route::get('/productos', [StorefrontController::class, 'products'])->name('store.products');
Route::get('/productos/{slug}', [StorefrontController::class, 'product'])->name('store.product');
Route::get('/categorias', [StorefrontController::class, 'categories'])->name('store.categories');
Route::get('/ofertas', [StorefrontController::class, 'offers'])->name('store.offers');
Route::get('/nosotros', [StorefrontController::class, 'about'])->name('store.about');
Route::get('/contacto', [StorefrontController::class, 'contact'])->name('store.contact');
Route::get('/carrito', [StorefrontController::class, 'cart'])->name('store.cart');
Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('store.checkout');
Route::get('/checkout/datos', function () { return view('store.checkout-step-2', ['currentCheckoutStep' => 1]); })->name('store.checkout.details');
Route::get('/checkout/pago', function () { return view('store.checkout-step-3', ['currentCheckoutStep' => 2]); })->name('store.checkout.payment');
Route::get('/checkout/exito', [StorefrontController::class, 'checkoutSuccess'])->name('store.checkout.success');
Route::get('/search', [StorefrontController::class, 'search'])->name('store.search');
Route::get('/privacidad', [StorefrontController::class, 'privacy'])->name('store.privacy');
Route::get('/cookies', [StorefrontController::class, 'cookies'])->name('store.cookies');
Route::middleware('auth')->group(function () {
	Route::get('/mi-perfil', [StorefrontController::class, 'profile'])->name('store.profile');
	Route::get('/mis-pedidos', [StorefrontController::class, 'orders'])->name('store.orders');
	Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

});

use App\Http\Controllers\PortalLogisticsController;
use App\Http\Controllers\PortalMarketingController;
use App\Http\Controllers\PortalStoreController;
use App\Http\Controllers\PortalAlliesController;

Route::middleware(['auth', 'role:portal'])->group(function () {
	Route::get('/admin/dashboard', [StorefrontController::class, 'portalDashboard'])->name('portal.dashboard');
	Route::get('/admin/tienda-cms', [PortalStoreController::class, 'index'])->name('portal.store');
	Route::get('/admin/marketing', [PortalMarketingController::class, 'index'])->name('portal.marketing');
	Route::get('/admin/scoring', [StorefrontController::class, 'portalScoring'])->name('portal.scoring');
	Route::get('/admin/inventario-catalogo', [StorefrontController::class, 'portalInventory'])->name('portal.inventory');
	Route::get('/admin/logistica', [PortalLogisticsController::class, 'index'])->name('portal.logistics');
	Route::get('/admin/convenios', [PortalAlliesController::class, 'index'])->name('portal.allies');
	Route::get('/admin/modulos/{module}', [StorefrontController::class, 'portalModule'])->name('portal.module');
	Route::get('/admin/proveedores/colores', [SupplierColorMappingController::class, 'index'])->name('portal.suppliers.colors');
	Route::post('/admin/inventario/{inventory}/movimientos', [InventoryMovementController::class, 'store'])->name('portal.inventory.movements.store');
	Route::post('/admin/proveedores/{supplier}/sincronizar', [SupplierSyncController::class, 'store'])->name('portal.suppliers.sync');
	Route::post('/admin/proveedores/{supplier}/colores', [SupplierColorMappingController::class, 'store'])->name('portal.suppliers.colors.store');
	Route::get('/admin/finanzas', [StorefrontController::class, 'portalFinance'])->name('portal.finance');
	Route::post('/admin/finanzas/payouts/{payoutBatch}/conciliar', [StorefrontController::class, 'reconcilePayoutBatch'])->name('portal.finance.payouts.reconcile');
	Route::post('/admin/finanzas/suscripciones/{subscription}/reintentar', [StorefrontController::class, 'retrySubscriptionDunning'])->name('portal.finance.subscriptions.retry');
	Route::post('/admin/finanzas/cuentas/{vendorBill}/pagar', [StorefrontController::class, 'payVendorBill'])->name('portal.finance.vendor_bills.pay');
	Route::post('/admin/marketing/cupones', [PortalMarketingController::class, 'storeCoupon'])->name('portal.marketing.coupons.store');
	Route::post('/admin/marketing/cupones/{coupon}/toggle', [PortalMarketingController::class, 'toggleCoupon'])->name('portal.marketing.coupons.toggle');
	Route::post('/admin/marketing/campanas/{adCampaign}/escalar', [PortalMarketingController::class, 'scaleCampaign'])->name('portal.marketing.campaigns.scale');
	Route::post('/admin/marketing/campanas/{adCampaign}/toggle', [PortalMarketingController::class, 'pauseCampaign'])->name('portal.marketing.campaigns.pause');
	Route::post('/admin/marketing/conversaciones/{conversation}/intervenir', [PortalMarketingController::class, 'takeoverConversation'])->name('portal.marketing.conversations.takeover');
	Route::post('/admin/marketing/email-automations/{emailAutomation}/toggle', [PortalMarketingController::class, 'toggleEmailAutomation'])->name('portal.marketing.automations.toggle');
	Route::post('/admin/logistica/manifiestos/cerrar', [PortalLogisticsController::class, 'closeManifest'])->name('portal.logistics.manifests.close');
	Route::post('/admin/logistica/rma/{rmaRequest}/inspeccionar', [PortalLogisticsController::class, 'inspectRma'])->name('portal.logistics.rma.inspect');
	Route::post('/admin/logistica/cajas/{packageType}/stock', [PortalLogisticsController::class, 'updatePackageStock'])->name('portal.logistics.packages.stock');
	Route::post('/admin/logistica/envios/{shipment}/status', [PortalLogisticsController::class, 'updateShipmentStatus'])->name('portal.logistics.shipments.status');

	// Hub 2: Tienda & CMS Multiverso
	Route::post('/admin/tienda-cms/fletes', [PortalStoreController::class, 'updateShippingRule'])->name('portal.store.shipping.update');
	Route::post('/admin/tienda-cms/banners/{bannerId}/toggle', [PortalStoreController::class, 'toggleBanner'])->name('portal.store.banners.toggle');
	Route::post('/admin/tienda-cms/marquesina/toggle', [PortalStoreController::class, 'toggleAnnouncement'])->name('portal.store.announcement.toggle');
	Route::post('/admin/tienda-cms/vitrinas/{universe}', [PortalStoreController::class, 'updateStorefront'])->name('portal.store.vitrinas.update');

	// Hub 7: Convenios & Aliados
	Route::post('/admin/convenios/canje/validar', [PortalAlliesController::class, 'validateTicket'])->name('portal.allies.ticket.validate');
	Route::post('/admin/convenios/aliados/{ally}/liquidar', [PortalAlliesController::class, 'executeSettlement'])->name('portal.allies.settlement.execute');
	Route::get('/admin/convenios/aliados/{ally}/export-csv', [PortalAlliesController::class, 'exportDoorListCsv'])->name('portal.allies.export.csv');
});

Route::get('/portal/aliado/{token?}', [PortalAlliesController::class, 'partnerPortal'])->name('portal.ally.extranet');
Route::post('/portal/aliado/canje/validar', [PortalAlliesController::class, 'validateTicket'])->name('portal.ally.extranet.validate');
Route::get('/portal/aliado/{token}/export-csv', [PortalAlliesController::class, 'exportDoorListCsvByToken'])->name('portal.ally.extranet.export.csv');

Route::get('/login', [PortalAccessController::class, 'userLogin'])->name('login');
Route::post('/login', [AuthModalController::class, 'login']);
Route::get('/ingreso/usuario', [PortalAccessController::class, 'userLogin'])->name('portal.user.login');
Route::post('/ingreso/usuario', [AuthModalController::class, 'login']);
Route::get('/ingreso/cliente', [PortalAccessController::class, 'clientLogin'])->name('portal.client.login');
Route::post('/ingreso/cliente', [AuthModalController::class, 'login']);

Route::post('/auth/login', [AuthModalController::class, 'login'])->name('auth.modal.login');
Route::post('/auth/logout', [AuthModalController::class, 'logout'])->name('auth.modal.logout');
Route::post('/logout', [AuthModalController::class, 'logout'])->name('logout');
Route::post('/auth/google/login', [AuthModalController::class, 'googleLogin'])->name('auth.modal.google.login');
Route::post('/auth/google/register', [AuthModalController::class, 'googleRegister'])->name('auth.modal.google.register');

// CRM Leads (móvil-first simple UI)
// CSV import routes
Route::get('leads/import', [App\Http\Controllers\LeadController::class, 'importForm'])->name('leads.import.form');
Route::post('leads/import', [App\Http\Controllers\LeadController::class, 'importProcess'])->name('leads.import.process');

Route::get('leads/board', [App\Http\Controllers\LeadController::class, 'board'])->name('leads.board');
Route::patch('leads/{lead}/status', [App\Http\Controllers\LeadController::class, 'update'])->name('leads.update');

Route::resource('leads', App\Http\Controllers\LeadController::class);

