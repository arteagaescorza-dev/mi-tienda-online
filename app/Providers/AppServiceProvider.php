<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;
use App\Observers\InventoryObserver;
use App\Observers\AllyLeadCaptureObserver;
use App\Models\AllyLeadCapture;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observadores de dominio para sincronización omnicanal inmutable Interhub 4 <-> 2 y Hub 7 <-> 3
        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        Inventory::observe(InventoryObserver::class);
        AllyLeadCapture::observe(AllyLeadCaptureObserver::class);

        // Directiva Blade condicional para visibilidad financiera (TASK-RBAC-03)
        \Illuminate\Support\Facades\Blade::if('canViewFinancials', function () {
            return auth()->check() && auth()->user()->canAccessFinancialData();
        });

        // Gate para autorizaciones de politicas financieras
        \Illuminate\Support\Facades\Gate::define('view-financial-data', function ($user) {
            return $user->canAccessFinancialData();
        });

        // Gate para validar acceso a hubs por rol
        \Illuminate\Support\Facades\Gate::define('access-hub', function ($user, string $hub) {
            return $user->canAccessHub($hub);
        });

        // Gates especificos de cada Hub basados en los permisos de User::canAccessHub
        \Illuminate\Support\Facades\Gate::define('view-dashboard', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('dashboard');
        });
        \Illuminate\Support\Facades\Gate::define('manage-cms', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('store');
        });
        \Illuminate\Support\Facades\Gate::define('manage-marketing', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('marketing');
        });
        \Illuminate\Support\Facades\Gate::define('manage-catalog', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('inventory');
        });
        \Illuminate\Support\Facades\Gate::define('manage-inventory', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('inventory');
        });
        \Illuminate\Support\Facades\Gate::define('manage-logistics', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('logistics');
        });
        \Illuminate\Support\Facades\Gate::define('manage-finance', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('finance');
        });
        \Illuminate\Support\Facades\Gate::define('manage-b2b', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && ($user->canAccessHub('allies') || $user->canAccessHub('vendor_extranet'));
        });
        \Illuminate\Support\Facades\Gate::define('manage-governance', function ($user = null) {
            $user = $user ?? auth()->user();
            return $user && $user->canAccessHub('governance');
        });

        // Compartir footerConfig y estado de marquesina globalmente en el layout Storefront PWA
        \Illuminate\Support\Facades\View::composer('layouts.store-pwa', function ($view) {
            $defaultFooterConfig = [
                'brand_summary' => 'Plataforma contemporánea de moda íntima, streetwear y autocuidado. Envíos nacionales directos vía paquetería express con empaque 100% neutro y precinto higiénico.',
                'whatsapp_contact' => '+52 55 7510 2015',
                'support_email' => 'hola@mitienda.online',
                'shipping_badge_text' => 'Paqueteria nacional express ($149 / Gratis +$999)',
                'packaging_badge_text' => 'Entrega personal discreta y segura en empaque 100% neutro',
                'payments_badge_text' => 'Pagos 100% seguros con Tarjeta, SPEI y OXXO Pay',
                'copyright_text' => '(C) 2026 MULTIVERSO STORE. Todos los derechos reservados.',
                'show_revops_link' => true,
            ];
            try {
                $footerConfig = \App\Models\CmsOfficialDocument::getByCode('STORE_FOOTER_CONFIG', $defaultFooterConfig);
            } catch (\Throwable $e) {
                $footerConfig = $defaultFooterConfig;
            }

            // 1. Categorías oficiales activas en footer
            $footerCategories = collect($footerConfig['footer_categories'] ?? [])
                ->filter(fn($c) => !empty($c['is_active']))
                ->sortBy('sort_order')
                ->values();
            
            if ($footerCategories->isEmpty()) {
                $footerCategories = \App\Models\Category::all()
                    ->map(fn($c, $idx) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                        'is_active' => true,
                        'sort_order' => $idx + 1,
                    ])->values();
            }

            // 2. Páginas de Información & Legal activas en footer (excluyendo páginas asociadas a productos/catálogo)
            $footerLegalPages = collect($footerConfig['footer_legal_pages'] ?? [])
                ->filter(fn($p) => !empty($p['is_active']))
                ->sortBy('sort_order')
                ->values();

            if ($footerLegalPages->isEmpty()) {
                $footerLegalPages = \App\Models\CmsPage::active()
                    ->inFooter()
                    ->get()
                    ->filter(fn($p) => !$p->hasCatalogFilters())
                    ->map(fn($p, $idx) => [
                        'id' => $p->id,
                        'title' => $p->title,
                        'slug' => $p->slug,
                        'is_active' => true,
                        'sort_order' => $p->footer_order ?: ($idx + 1),
                    ])->values();
            }

            $view->with('footerConfig', $footerConfig ?? $defaultFooterConfig);
            $view->with('footerCategories', $footerCategories);
            $view->with('footerLegalPages', $footerLegalPages);

            $viewData = $view->getData();
            if (!array_key_exists('announcementActive', $viewData)) {
                $announcementActive = \Illuminate\Support\Facades\Cache::get('cms_announcement_active');
                if ($announcementActive === null) {
                    try {
                        $annDoc = \App\Models\CmsOfficialDocument::getByCode('STORE_ANNOUNCEMENTS_CONFIG');
                        $announcementActive = is_array($annDoc) && array_key_exists('active', $annDoc)
                            ? (bool) $annDoc['active']
                            : true;
                    } catch (\Throwable $e) {
                        $announcementActive = true;
                    }
                }
                $view->with('announcementActive', (bool) $announcementActive);
            }
        });
    }
}
