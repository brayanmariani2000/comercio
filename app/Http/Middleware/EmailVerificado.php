<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificado
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

        // Verificar si el email está verificado
        if (!auth()->user()->hasVerifiedEmail()) {
            // Rutas que no requieren verificación
            $excluidas = [
                'verification.notice',
                'verification.verify',
                'verification.send',
                'logout',
                'verification.resend',
            ];
            
            if (in_array($request->route()->getName(), $excluidas)) {
                return $next($request);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes verificar tu dirección de email antes de continuar',
                    'requires_verification' => true,
                    'email' => auth()->user()->email
                ], 403);
            }
            
            return redirect()->route('verification.notice')
                ->with('warning', 'Verifica tu dirección de email para acceder a todas las funcionalidades');
        }

        // Verificar si el usuario está verificado (para Venezuela con cédula/RIF)
        if (!auth()->user()->verificado) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta requiere verificación adicional',
                    'requires_document_verification' => true
                ], 403);
            }
            
            return redirect()->route('verificacion.pendiente')
                ->with('info', 'Tu cuenta requiere verificación adicional de documentos');
        }

        return $next($request);
    }
}