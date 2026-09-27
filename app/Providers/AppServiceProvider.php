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
    }
}
