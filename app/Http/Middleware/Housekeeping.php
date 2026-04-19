<?php

namespace App\Http\Middleware;

use Closure;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class Housekeeping
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
        if (auth()->user()->roles->contains('slug', 'housekeeping')) {
            // return redirect('/');
        }
        return $next($request);
    }
}
