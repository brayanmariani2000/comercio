<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCache
{
    /**
     * Evita que el navegador almacene en caché páginas sensibles.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, private')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
    }
}
