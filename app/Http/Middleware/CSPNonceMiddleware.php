<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CSPNonceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Generate a secure random nonce
        $nonce = base64_encode(random_bytes(16));

        // Share the nonce globally with all views
        view()->share('nonce', $nonce);

        return $next($request);
    }
}
