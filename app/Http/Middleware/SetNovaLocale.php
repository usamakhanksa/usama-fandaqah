<?php

namespace App\Http\Middleware;

use Closure;
use Laravel\Nova\Nova;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Events\ServingNova;

class SetNovaLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $default = config('nova.default_locale', 'en');
        $locales = config('nova.locales', []);
        $locales_cp = config('nova.locales_cp', []);
        if (auth()->check()) {

            if(in_array( auth()->user()->id,explode(',',env('SUSPENDED_USERS')))){
                $request->session()->invalidate();
            }

            if(auth()->user()->is_suspended){
                $request->session()->invalidate();
            }

            $locale = session('locale');
            if (isset($locales[$locale])) {
                app()->setLocale($locale);
                if (isset($locales_cp[$locale])) {
                    setlocale(LC_ALL, $locales_cp[$locale]);
                }
            } else {
                app()->setLocale($default);
                if (isset($locales_cp[$default])) {
                    setlocale(LC_ALL, $locales_cp[$default]);
                }
            }
        } else {
            $locale = session('locale');
            if (isset($locales[$locale])) {
                app()->setLocale($locale);
                session(['locale' => $locale]);
                if (isset($locales_cp[$locale])) {
                    setlocale(LC_ALL, $locales_cp[$locale]);
                }
            } else {
                app()->setLocale($default);
                session(['locale' => $default]);
                if (isset($locales_cp[$default])) {
                    setlocale(LC_ALL, $locales_cp[$default]);
                }
            }
        }

        $locale = \App::getLocale();
        $rtlLocales = config('app.novaRTL', ['ar']);
        if (in_array($locale, $rtlLocales)) {
            Nova::serving(function (ServingNova $event) {
                Nova::style('nova-volve-rtl', base_path() . '/vendor/mustafakhaleddev/nova-rtl-support/resources/css/theme.css');
                Nova::style('nova-volve-rtl', base_path() . '/public/css/nova-rtl.css');
            });
        }

        return $next($request);
    }
}
