<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\Gate::define('view-dashboard', function ($user) {
            return $user->canAccessHub('dashboard');
        });
        \Illuminate\Support\Facades\Gate::define('manage-cms', function ($user) {
            return $user->canAccessHub('store');
        });
        \Illuminate\Support\Facades\Gate::define('manage-marketing', function ($user) {
            return $user->canAccessHub('marketing');
        });
        \Illuminate\Support\Facades\Gate::define('manage-catalog', function ($user) {
            return $user->canAccessHub('inventory');
        });
        \Illuminate\Support\Facades\Gate::define('manage-inventory', function ($user) {
            return $user->canAccessHub('inventory');
        });
        \Illuminate\Support\Facades\Gate::define('manage-logistics', function ($user) {
            return $user->canAccessHub('logistics');
        });
        \Illuminate\Support\Facades\Gate::define('manage-finance', function ($user) {
            return $user->canAccessHub('finance');
        });
        \Illuminate\Support\Facades\Gate::define('manage-b2b', function ($user) {
            return $user->canAccessHub('allies');
        });
        \Illuminate\Support\Facades\Gate::define('manage-governance', function ($user) {
            return $user->canAccessHub('governance');
        });

        // Compartir footerConfig globalmente en el layout Storefront PWA
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
                'show_zero_emojis_badge' => true,
            ];
            try {
                $footerConfig = \App\Models\CmsOfficialDocument::getByCode('STORE_FOOTER_CONFIG', $defaultFooterConfig);
            } catch (\Throwable $e) {
                $footerConfig = $defaultFooterConfig;
            }
            $view->with('footerConfig', $footerConfig ?? $defaultFooterConfig);
        });
    }
}
