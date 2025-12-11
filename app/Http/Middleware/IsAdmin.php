<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
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

        if (!auth()->user()->esAdministrador()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso no autorizado. Se requiere rol de administrador.'
                ], 403);
            }
            
            // Registrar intento de acceso no autorizado
            \App\Models\BitacoraSistema::registrar(
                auth()->id(),
                'acceso_no_autorizado',
                null,
                null,
                "Intento de acceso a ruta de administrador: {$request->fullUrl()}",
                null,
                null
            );
            
            return redirect()->route('home')->with('error', 'Acceso no autorizado');
        }

        // Verificar que el admin esté activo
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