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

    public function boot(): void
    {
        $this->app->singleton(\Laravel\Fortify\Contracts\LoginViewResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\LoginViewResponse {
                public function toResponse($request)
                {
                    return view('app');
                }
            };
        });
    }
}