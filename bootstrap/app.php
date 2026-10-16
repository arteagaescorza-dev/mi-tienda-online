<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'admin/tienda-cms/*',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'financial.privacy' => \App\Http\Middleware\EnforceFinancialPrivacy::class,
            'entity.isolation' => \App\Http\Middleware\EnsureEntityIsolation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu sesión se actualizó. Intenta de nuevo.'], 419);
            }

            return redirect()->back()->withInput($request->except(['_token', 'password']))
                ->with('error', 'El token del formulario expiró por inactividad y fue renovado automáticamente. Por favor vuelve a guardar.');
        });
    })->create();
