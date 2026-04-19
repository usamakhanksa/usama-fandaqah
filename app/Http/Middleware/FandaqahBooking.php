<?php

namespace App\Http\Middleware;

use Closure;

class FandaqahBooking
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
        app()->setLocale($request->headers->get('x-language') ? $request->headers->get('x-language') : 'ar');
        return $next($request);
    }
}
