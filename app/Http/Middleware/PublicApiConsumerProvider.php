<?php

namespace App\Http\Middleware;

use Closure;

class PublicApiConsumerProvider
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
        config(['auth.guards.api.provider' => 'public_api_consumers']);
        return $next($request);
    }
}
