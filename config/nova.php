<?php

use App\Http\Middleware\NovaStaticsMiddleware;
use Laravel\Nova\Http\Middleware\Authorize;
use Laravel\Nova\Http\Middleware\BootTools;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Http\Middleware\DispatchServingNovaEvent;

return [
    /**
     * Switchable locales
     */
    'locales' => [
        'en' => 'English',
        'ar' => 'Arabic',
    ],

    /**
     * You must have installed theese locales at yoiur system to use all locale features.
     * used as `setlocale( LC_ALL, <LOCALE CODE PAGE>)` at middleware
     * Optional.
     */
    'locales_cp' => [
        'en' => 'en_US.UTF-8',
        'ar' => 'ar_SA.UTF-8',
    ],

    'default_locale' => 'ar',

    /*
    |--------------------------------------------------------------------------
    | Nova App Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to display the name of the application within the UI
    | or in other locations. Of course, you're free to change the value.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Nova App URL
    |--------------------------------------------------------------------------
    |
    | This URL is where users will be directed when clicking the application
    | name in the Nova navigation bar. You are free to change this URL to
    | any location you wish depending on the needs of your application.
    |
    */

    'url' => env('APP_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Nova Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Nova will be accessible from. Feel free to
    | change this path to anything you like. Note that this URI will not
    | affect Nova's internal API routes which aren't exposed to users.
    |
    */

    'path' => '/home',

    /*
    |--------------------------------------------------------------------------
    | Nova Authentication Guard
    |--------------------------------------------------------------------------
    |
    | This configuration option defines the authentication guard that will
    | be used to protect your Nova routes. This option should match one
    | of the authentication guards defined in the "auth" config file.
    |
    */

    'guard' => env('NOVA_GUARD', null),

    /*
    |--------------------------------------------------------------------------
    | Nova Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Nova route, giving you the
    | chance to add your own middleware to this stack or override any of
    | the existing middleware. Or, you can just stick with this stack.
    |
    */

    'middleware' => [
        'web',
        Authenticate::class,
        NovaStaticsMiddleware::class,
        DispatchServingNovaEvent::class,
        BootTools::class,
        Authorize::class,
        \Laravel\Spark\Http\Middleware\VerifyUserHasTeam::class,
        \App\Http\Middleware\SetNovaLocale::class,
        \App\Http\Middleware\Housekeeping::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova Pagination Type
    |--------------------------------------------------------------------------
    |
    | This option defines the pagination visual style used by Resources. You
    | may choose between two types: "simple" and "links". Feel free to set
    | this option to the visual style you like for your application.
    |
    */

    'pagination' => 'links',

];
