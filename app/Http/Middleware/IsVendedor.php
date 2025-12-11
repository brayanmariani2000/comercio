<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsVendedor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado'
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!auth()->user()->esVendedor()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso no autorizado. Se requiere ser vendedor.'
                ], 403);
            }
            
            // Redirigir a página para convertirse en vendedor
            return redirect()->route('convertirse.vendedor')->with('info', 'Debes registrarte como vendedor para acceder a esta sección');
        }

        // Verificar que el usuario esté activo
        if (auth()->user()->suspendido) {
            auth()->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuenta suspendida'
                ], 403);
            }
            
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido suspendida');
        }

        // Actualizar último acceso
        auth()->user()->update(['ultimo_acceso' => now()]);

        return $next($request);
    }
}