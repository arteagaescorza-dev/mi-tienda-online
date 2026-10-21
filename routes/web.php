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
Route::get('/catalogo', [StorefrontController::class, 'catalog'])->name('store.catalog');
Route::get('/explorar', [StorefrontController::class, 'explore'])->name('store.explore');
Route::get('/productos', [StorefrontController::class, 'products'])->name('store.products');
Route::get('/productos/{slug}', [StorefrontController::class, 'product'])->name('store.product');
Route::get('/revista', [StorefrontController::class, 'magazineIndex'])->name('store.magazine');
Route::get('/revista/{slug}', [StorefrontController::class, 'magazinePost'])->name('store.magazine.post');
Route::get('/pagina/{slug}', [StorefrontController::class, 'cmsPage'])->name('store.page');
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
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('store.sitemap');
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
	Route::get('/admin', fn () => redirect()->route('portal.dashboard'));
	Route::get('/admin/dashboard', [StorefrontController::class, 'portalDashboard'])->name('portal.dashboard');
	Route::get('/admin/tienda-cms', [PortalStoreController::class, 'index'])->name('portal.store');
	Route::get('/admin/marketing', [PortalMarketingController::class, 'index'])->name('portal.marketing');
	Route::get('/admin/scoring', [StorefrontController::class, 'portalScoring'])->name('portal.scoring');
	Route::get('/admin/inventario-catalogo', [StorefrontController::class, 'portalInventory'])->name('portal.inventory');
	Route::get('/admin/logistica', [PortalLogisticsController::class, 'index'])->name('portal.logistics');
	Route::get('/admin/convenios', [PortalAlliesController::class, 'index'])->name('portal.allies');
	Route::get('/admin/gobernanza', [\App\Http\Controllers\PortalGovernanceController::class, 'index'])->name('portal.governance');
	Route::post('/admin/gobernanza/entorno', [\App\Http\Controllers\PortalGovernanceController::class, 'updateEnvironment'])->name('portal.governance.environment.update');
	Route::get('/admin/modulos/{module}', [StorefrontController::class, 'portalModule'])->name('portal.module');
	Route::get('/admin/proveedores/colores', [SupplierColorMappingController::class, 'index'])->name('portal.suppliers.colors');
	Route::post('/admin/inventario/{inventory}/movimientos', [InventoryMovementController::class, 'store'])->name('portal.inventory.movements.store');
	Route::post('/admin/proveedores/{supplier}/sincronizar', [SupplierSyncController::class, 'store'])->name('portal.suppliers.sync');
	Route::post('/admin/proveedores/{supplier}/colores', [SupplierColorMappingController::class, 'store'])->name('portal.suppliers.colors.store');
});

Route::middleware('auth')->group(function () {
	Route::post('/admin/roles/switch', [PortalAccessController::class, 'switchRole'])->name('portal.roles.switch');
});

Route::middleware(['auth', 'role:portal'])->group(function () {

	Route::middleware('financial.privacy')->group(function () {
		Route::get('/admin/finanzas', [\App\Http\Controllers\PortalFinanceController::class, 'index'])->name('portal.finance');
		Route::post('/admin/finanzas/payouts/{payoutBatch}/conciliar', [\App\Http\Controllers\PortalFinanceController::class, 'reconcilePayoutBatch'])->name('portal.finance.payouts.reconcile');
		Route::post('/admin/finanzas/suscripciones/{subscription}/reintentar', [\App\Http\Controllers\PortalFinanceController::class, 'retrySubscriptionDunning'])->name('portal.finance.subscriptions.retry');
		Route::post('/admin/finanzas/cuentas/{vendorBill}/pagar', [\App\Http\Controllers\PortalFinanceController::class, 'payVendorBill'])->name('portal.finance.vendor_bills.pay');
		Route::post('/admin/finanzas/auditoria-envios/cargar', [\App\Http\Controllers\PortalFinanceController::class, 'uploadShippingInvoiceReport'])->name('portal.finance.shipping_audit.upload');
		Route::post('/admin/finanzas/auditoria-envios/{reconciliation}/reclamar', [\App\Http\Controllers\PortalFinanceController::class, 'fileShippingClaim'])->name('portal.finance.shipping_audit.claim');
		Route::post('/portal/finance/shipping-audit/{reconciliation}/claim', [\App\Http\Controllers\PortalFinanceController::class, 'fileShippingClaim']);
		Route::post('/admin/finanzas/cotejo-3-vias/verificar', [\App\Http\Controllers\PortalFinanceController::class, 'verifyThreeWayMatching'])->name('portal.finance.three_way_matching.verify');
		Route::post('/admin/finanzas/cotejo-3-vias/{id}/nota-debito', [\App\Http\Controllers\PortalFinanceController::class, 'issueDebitNoteWeb'])->name('portal.finance.three_way_matching.debit_note');
		Route::post('/admin/finanzas/cotejo-3-vias/{id}/autorizar-spei', [\App\Http\Controllers\PortalFinanceController::class, 'authorizeSpeiPayoutWeb'])->name('portal.finance.three_way_matching.authorize_spei');
	});
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
	Route::post('/admin/logistica/envios/{shipment}/empacar', [PortalLogisticsController::class, 'fulfillPackaging'])->name('portal.logistics.shipments.fulfill_packaging');

	// Hub 2: Tienda & CMS Multiverso
	Route::post('/admin/tienda-cms/fletes', [PortalStoreController::class, 'updateShippingRule'])->name('portal.store.shipping.update');
	Route::post('/admin/tienda-cms/banners/{bannerId}/toggle', [PortalStoreController::class, 'toggleBanner'])->name('portal.store.banners.toggle');
	Route::post('/admin/tienda-cms/marquesina/toggle', [PortalStoreController::class, 'toggleAnnouncement'])->name('portal.store.announcement.toggle');
	Route::post('/admin/tienda-cms/vitrinas/{universe}', [PortalStoreController::class, 'updateStorefront'])->name('portal.store.vitrinas.update');
	Route::post('/admin/tienda-cms/secciones', [PortalStoreController::class, 'storeSection'])->name('portal.store.sections.store');
	Route::put('/admin/tienda-cms/secciones/{section}', [PortalStoreController::class, 'updateSection'])->name('portal.store.sections.update');
	Route::post('/admin/tienda-cms/secciones/{section}/archivar', [PortalStoreController::class, 'archiveSection'])->name('portal.store.sections.archive');
	Route::post('/admin/tienda-cms/secciones/reorder', [PortalStoreController::class, 'reorderSections'])->name('portal.store.sections.reorder');
	Route::post('/admin/tienda-cms/secciones/{section}/toggle', [PortalStoreController::class, 'toggleSection'])->name('portal.store.sections.toggle');
	Route::delete('/admin/tienda-cms/secciones/{section}', [PortalStoreController::class, 'destroySection'])->name('portal.store.sections.destroy');
	Route::post('/admin/tienda-cms/documentos/{code}', [PortalStoreController::class, 'updateDocument'])->name('portal.store.documents.update');
	Route::post('/admin/tienda-cms/paginas', [PortalStoreController::class, 'storePage'])->name('portal.store.pages.store');
	Route::post('/admin/tienda-cms/paginas/reorder', [PortalStoreController::class, 'reorderPages'])->name('portal.store.pages.reorder');
	Route::put('/admin/tienda-cms/paginas/{page}', [PortalStoreController::class, 'updatePage'])->name('portal.store.pages.update');
	Route::post('/admin/tienda-cms/paginas/{page}/toggle-status', [PortalStoreController::class, 'togglePageStatus'])->name('portal.store.pages.toggle-status');
	Route::post('/admin/tienda-cms/paginas/{page}/archivar', [PortalStoreController::class, 'archivePage'])->name('portal.store.pages.archive');
	Route::delete('/admin/tienda-cms/paginas/{page}', [PortalStoreController::class, 'destroyPage'])->name('portal.store.pages.destroy');
	Route::post('/admin/tienda-cms/anuncios/actualizar', [PortalStoreController::class, 'updateAnnouncements'])->name('portal.store.announcements.update');
	Route::post('/admin/tienda-cms/blog', [PortalStoreController::class, 'storeBlogPost'])->name('portal.store.blog.store');
	Route::put('/admin/tienda-cms/blog/{post}', [PortalStoreController::class, 'updateBlogPost'])->name('portal.store.blog.update');
	Route::delete('/admin/tienda-cms/blog/{post}', [PortalStoreController::class, 'destroyBlogPost'])->name('portal.store.blog.destroy');
	Route::post('/admin/tienda-cms/tracking', [PortalStoreController::class, 'updateTracking'])->name('portal.store.tracking.update');
	Route::post('/admin/tienda-cms/filtros', [PortalStoreController::class, 'updateFilters'])->name('portal.store.filters.update');
	Route::get('/admin/tienda-cms/media', [PortalStoreController::class, 'apiGetMedia'])->name('portal.store.media.get');
	Route::post('/admin/tienda-cms/media', [PortalStoreController::class, 'apiUploadMedia'])->name('portal.store.media.upload');
	Route::delete('/admin/tienda-cms/media/{id}', [PortalStoreController::class, 'apiDeleteMedia'])->name('portal.store.media.delete');

	// Hub 7: Convenios & Aliados
	Route::post('/admin/convenios/canje/validar', [PortalAlliesController::class, 'validateTicket'])->name('portal.allies.ticket.validate');
	Route::post('/admin/convenios/campanas/{id}/aprobar', [PortalAlliesController::class, 'approveCampaign'])->name('portal.allies.campaign.approve');
	Route::post('/admin/convenios/campanas/{id}/rechazar', [PortalAlliesController::class, 'rejectCampaign'])->name('portal.allies.campaign.reject');
	Route::post('/admin/convenios/aliados', [PortalAlliesController::class, 'storeAlly'])->name('portal.allies.store');
	Route::put('/admin/convenios/aliados/{ally}', [PortalAlliesController::class, 'updateAlly'])->name('portal.allies.update');
	Route::post('/admin/convenios/aliados/{ally}/archivar', [PortalAlliesController::class, 'archiveAlly'])->name('portal.allies.archive');
	Route::delete('/admin/convenios/aliados/{ally}', [PortalAlliesController::class, 'destroyAlly'])->name('portal.allies.destroy');
	Route::post('/admin/convenios/aliados/{id}/restaurar', [PortalAlliesController::class, 'restoreAlly'])->name('portal.allies.restore');
	Route::post('/admin/convenios/aliados/{ally}/liquidar', [PortalAlliesController::class, 'executeSettlement'])->name('portal.allies.settlement.execute');
	Route::get('/admin/convenios/aliados/{ally}/export-csv', [PortalAlliesController::class, 'exportDoorListCsv'])->name('portal.allies.export.csv');
});

// Hub 7: Extranet de Proveedores & Fabricantes (TASK-HUB-VND)
use App\Http\Controllers\PortalVendorController;

Route::middleware(['auth', 'entity.isolation:vendor'])->group(function () {
	Route::get('/admin/extranet/proveedores', [PortalVendorController::class, 'index'])->name('portal.vendor.extranet');
	Route::get('/admin/extranet/proveedores/ordenes/{id}/imprimir', [PortalVendorController::class, 'printOrder'])->name('portal.vendor.orders.print');
});

Route::get('/portal/aliado/{token?}', [PortalAlliesController::class, 'partnerPortal'])->name('portal.ally.extranet');
Route::post('/portal/aliado/{token}/campanas', [PortalAlliesController::class, 'submitCampaign'])->name('portal.ally.extranet.campaign.submit');
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

