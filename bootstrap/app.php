<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        // Core Providers
        App\Providers\AppServiceProvider::class,
        Laravel\Tinker\TinkerServiceProvider::class,
        Filament\FilamentServiceProvider::class,
        Spatie\Translatable\TranslatableServiceProvider::class,
        App\Providers\Filament\AdminPanelProvider::class,

        // Package Providers
        R64\NovaFields\FieldServiceProvider::class,
        App\Providers\NovaServiceProvider::class,
        niklasravnsborg\LaravelPdf\PdfServiceProvider::class,
    ])
    ->withRouting(
        using: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->create();