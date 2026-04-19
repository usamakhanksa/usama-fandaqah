<?php

namespace App\Http\Middleware;

use App\Team;
use Closure;
use Illuminate\Http\Request;

class FetchCurrentTeamFromUrl
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->headers->has('x-team'), 404);
        
        /**
         * @todo : remember to remove the below line it's totally wrong
         */
        app()->setLocale($request->headers->get('x-language') ? $request->headers->get('x-language') : 'ar');

        $team = Team::where('id', $request->headers->get('x-team'))->orWhere('slug' , $request->headers->get('x-team'))->first();
        

        abort_unless(!is_null($team), 404);
        
       

        \View::share('currentTeamSlug', $team->slug);
        \View::share('currentTeamId', $team->id);

        return $next($request);
    }
}
