<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
            
            // Modular route files
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/dashboard.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/reservations.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/frontdesk.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/rooms.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/guests.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/finance.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/nightaudit.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/reports.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/marketing.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/website.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/settings.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/pos.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/integrations.php'));
            
            Route::middleware(['web', 'auth', 'team.scope'])
                ->namespace($this->namespace)
                ->group(base_path('routes/system.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}