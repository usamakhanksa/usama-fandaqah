<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(\App\Providers\ReservationModuleServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
  public function boot(): void
{
    if (env('APP_URL')) {
        URL::forceRootUrl(config('app.url'));
    }

    // Force HTTPS when behind ngrok
    if (request()->header('X-Forwarded-Proto') === 'https') {
        URL::forceScheme('https');
    }
}
}