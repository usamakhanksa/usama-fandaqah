<?php
namespace App\Http;
use App\Http\Middleware\FandaqahBooking;
use App\Http\Middleware\FetchCurrentTeamFromUrl;
use App\Http\Middleware\HasManageTeam;
use App\Http\Middleware\SetNovaLocale;
use App\Http\Middleware\WithTeamSlug;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\PassportCustomProviderAccessToken::class,
        \App\Http\Middleware\CSPNonceMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
//            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Laravel\Spark\Http\Middleware\CreateFreshApiToken::class,
            SetNovaLocale::class
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
            'localization',
            \Barryvdh\Cors\HandleCors::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'dev' => \Laravel\Spark\Http\Middleware\VerifyUserIsDeveloper::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'hasTeam' => \Laravel\Spark\Http\Middleware\VerifyUserHasTeam::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'subscribed' => \Laravel\Spark\Http\Middleware\VerifyUserIsSubscribed::class,
        'teamSubscribed' => \Laravel\Spark\Http\Middleware\VerifyTeamIsSubscribed::class,
        'localization' => \App\Http\Middleware\localization::class,
        'fetchTeam' => FetchCurrentTeamFromUrl::class,
        'withTeam' => WithTeamSlug::class,
        'hasManageTeam' => HasManageTeam::class,
        'passport-administrators' => \App\Http\Middleware\PassportCustomProvider::class,
        'fandaqah.booking' => FandaqahBooking::class,
        'check.header' => \App\Http\Middleware\MyTravel::class,

    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,

    ];
}
