<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendedorVerificado
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado'
                ], 401);
            }
            return redirect()->route('login');
        }

        // Verificar que sea vendedor
        if (!$user->esVendedor()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No eres vendedor'
                ], 403);
            }
            return redirect()->route('home')->with('error', 'No eres vendedor');
        }

        // Verificar que tenga vendedor asociado
        if (!$user->vendedor) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de vendedor no encontrado'
                ], 403);
            }
            return redirect()->route('completar.perfil.vendedor')->with('warning', 'Completa tu perfil de vendedor');
        }

        // Verificar que el vendedor esté verificado
        if (!$user->vendedor->verificado) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta de vendedor está en proceso de verificación. Por favor espera la aprobación.',
                    'estado' => 'pendiente_verificacion'
                ], 403);
            }
            
            return redirect()->route('vendedor.verificacion.pendiente')
                ->with('info', 'Tu cuenta de vendedor está en proceso de verificación. Te notificaremos cuando sea aprobada.');
        }

        // Verificar que el vendedor esté activo
        if (!$user->vendedor->activo) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta de vendedor ha sido desactivada'
                ], 403);
            }
            
            return redirect()->route('vendedor.cuenta.desactivada')
                ->with('error', 'Tu cuenta de vendedor ha sido desactivada. Contacta con soporte.');
        }

        // Verificar membresía activa si aplica
        if ($user->vendedor->fecha_vencimiento_membresia && 
            $user->vendedor->fecha_vencimiento_membresia->isPast()) {
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu membresía ha expirado. Renueva para continuar.',
                    'estado' => 'membresia_expirada'
                ], 403);
            }
            
            return redirect()->route('vendedor.membresia.expirada')
                ->with('warning', 'Tu membresía ha expirado. Renueva para continuar vendiendo.');
        }

        return $next($request);
    }
}