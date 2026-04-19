<?php

namespace Surelab\Theme;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Nova::serving(function (ServingNova $event) {
            Nova::style('theme', __DIR__ . '/../resources/css/theme.css');
            Nova::remoteScript(asset('vendor/nova/jquery.js'));
            Nova::remoteScript(asset('vendor/nova/arrive.js'));
            Nova::remoteScript(asset('vendor/nova/hashids.js'));
            Nova::script('theme', __DIR__ . '/../resources/js/theme.js');
        });



    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
