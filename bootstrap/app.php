<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        // Only keep known working providers
        App\Providers\AppServiceProvider::class,
        R64\NovaFields\FieldServiceProvider::class,

        // Commented old/abandoned providers
        App\Providers\NovaServiceProvider::class,
        // Alfa6661\AutoNumber\AutoNumberServiceProvider::class,
        // AlexBowers\MultipleDashboard\ToolServiceProvider::class,
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