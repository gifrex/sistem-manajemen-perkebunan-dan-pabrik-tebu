<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use App\Services\PermissionService;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        using: function () {
            // Web routes with web middleware
            Route::middleware('web')->group(base_path('routes/web.php'));
            Route::middleware('web')->group(base_path('routes/react.php'));
            Route::middleware('web')->group(base_path('routes/masterdata.php'));
            Route::middleware('web')->group(base_path('routes/transaction.php'));
            Route::middleware('web')->group(base_path('routes/report.php'));
            Route::middleware('web')->group(base_path('routes/dashboard.php'));
            Route::middleware('web')->group(base_path('routes/process.php'));
            Route::middleware('web')->group(base_path('routes/user-management.php'));
            Route::middleware('web')->group(base_path('routes/pabrik.php'));
            Route::middleware('web')->group(base_path('routes/infoupdates.php'));
            Route::middleware('web')->group(base_path('routes/approval.php'));
            Route::middleware('web')->group(base_path('routes/it-support.php'));
            Route::middleware('web')->group(base_path('routes/settings.php'));
            Route::middleware('web')->group(base_path('routes/finance.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'mandor.access' => \App\Http\Middleware\MandorAccessManagement::class,
            'api.client.active' => \App\Http\Middleware\CheckApiClientActive::class,
        ]);

        // Apply mandor access management globally to web routes
        $middleware->web(append: [
            \App\Http\Middleware\SwitchDatabase::class,
            \App\Http\Middleware\MandorAccessManagement::class,
        ]);

        // CSRF exceptions
        $middleware->validateCsrfTokens(except: [
            'dashboard/mapsapi',
            'api/mobile/*',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->booting(function () {
        // Dynamic Gate untuk Laravel @can, @canany directives
        Gate::before(function ($user, $ability) {
            return PermissionService::check($user, $ability) ?: null;
        });
    })
    ->create();