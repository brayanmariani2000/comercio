<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsComprador
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

        if (!auth()->user()->esComprador()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso no autorizado. Se requiere ser comprador.'
                ], 403);
            }
            
            // Si es vendedor, redirigir a dashboard de vendedor
            if (auth()->user()->esVendedor()) {
                return redirect()->route('vendedor.dashboard')->with('info', 'Ya eres vendedor, accede desde tu panel');
            }
            
            return redirect()->route('home')->with('error', 'Acceso no autorizado');
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

        // Verificar email verificado
        if (!$request->routeIs('verification.*') && !auth()->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes verificar tu email antes de continuar'
                ], 403);
            }
            
            return redirect()->route('verification.notice')->with('warning', 'Verifica tu email para continuar');
        }

        // Actualizar último acceso
        auth()->user()->update(['ultimo_acceso' => now()]);

        return $next($request);
    }
}