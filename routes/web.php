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
});

Route::get('/portal/aliado/{token?}', [PortalAlliesController::class, 'partnerPortal'])->name('portal.ally.extranet');

Route::get('/ingreso/usuario', [PortalAccessController::class, 'userLogin'])->name('portal.user.login');
Route::get('/ingreso/cliente', [PortalAccessController::class, 'clientLogin'])->name('portal.client.login');

Route::post('/auth/login', [AuthModalController::class, 'login'])->name('auth.modal.login');
Route::post('/auth/logout', [AuthModalController::class, 'logout'])->name('auth.modal.logout');
Route::post('/auth/google/login', [AuthModalController::class, 'googleLogin'])->name('auth.modal.google.login');
Route::post('/auth/google/register', [AuthModalController::class, 'googleRegister'])->name('auth.modal.google.register');

// CRM Leads (móvil-first simple UI)
// CSV import routes
Route::get('leads/import', [App\Http\Controllers\LeadController::class, 'importForm'])->name('leads.import.form');
Route::post('leads/import', [App\Http\Controllers\LeadController::class, 'importProcess'])->name('leads.import.process');

Route::get('leads/board', [App\Http\Controllers\LeadController::class, 'board'])->name('leads.board');
Route::patch('leads/{lead}/status', [App\Http\Controllers\LeadController::class, 'update'])->name('leads.update');

Route::resource('leads', App\Http\Controllers\LeadController::class);

