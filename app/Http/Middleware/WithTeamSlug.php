<?php

namespace App\Http\Middleware;

use App\Team;
use Closure;

class WithTeamSlug
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $params = \Route::current()->parameters();
        $hosts = explode('.', $_SERVER['HTTP_HOST']);

        abort_unless(isset($params['account']), 404);

        $team = Team::where('slug', $params['account'])->first();
        abort_unless(!is_null($team), 404);
        if (!$team->enable_website) {
            return \Response::view('website_not_enabled', ['name'   =>  $team->name]);
        }

        \View::share('currentTeamSlug', $team->slug);
        \View::share('currentTeamId', $team->id);

        if (count($hosts) < 3) {
            $domain = 'http://' . $team->slug . '.' . env('MAIN_DOMAIN');
            $domain = $domain . $request->getRequestUri();
            return \Redirect::to($domain);
        }

        return $next($request);
    }
}
