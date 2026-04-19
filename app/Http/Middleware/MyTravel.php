<?php

namespace App\Http\Middleware;

use Closure;

class MyTravel 
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

        $headerKey = $request->header('MyTravel');
        $envValue = env('MY_TRAVEL_KEY');

        if ($headerKey != $envValue) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
