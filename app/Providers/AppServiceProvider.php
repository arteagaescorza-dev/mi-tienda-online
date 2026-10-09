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

        // Gate para ver el dashboard
        \Illuminate\Support\Facades\Gate::define('view-dashboard', function ($user) {
            return $user->canAccessHub('dashboard');
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
