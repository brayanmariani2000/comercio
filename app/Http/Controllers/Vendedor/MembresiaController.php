<?php
namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MembresiaController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'planes' => [
                [
                    'codigo' => 'basico',
                    'nombre' => 'Básico',
                    'precio_mensual' => 0,
                    'limite_productos' => 50,
                    'comision' => '15%',
                    'soporte' => 'Email',
                    'caracteristicas' => [
                        'Productos ilimitados' => false,
                        'Destacado en búsquedas' => false,
                        'Estadísticas avanzadas' => false,
                        'Soporte prioritario' => false
                    ]
                ],
                [
                    'codigo' => 'profesional',
                    'nombre' => 'Profesional',
                    'precio_mensual' => 25000, // Bs.
                    'limite_productos' => 200,
                    'comision' => '12%',
                    'soporte' => 'Chat + Email',
                    'caracteristicas' => [
                        'Productos ilimitados' => false,
                        'Destacado en búsquedas' => true,
                        'Estadísticas avanzadas' => true,
                        'Soporte prioritario' => false
                    ]
                ],
                [
                    'codigo' => 'premium',
                    'nombre' => 'Premium',
                    'precio_mensual' => 45000,
                    'limite_productos' => 1000,
                    'comision' => '8%',
                    'soporte' => 'Chat + Teléfono + Email',
                    'caracteristicas' => [
                        'Productos ilimitados' => false,
                        'Destacado en búsquedas' => true,
                        'Estadísticas avanzadas' => true,
                        'Soporte prioritario' => true
                    ]
                ],
                [
                    'codigo' => 'ilimitado',
                    'nombre' => 'Ilimitado',
                    'precio_mensual' => 75000,
                    'limite_productos' => 0, // ilimitado
                    'comision' => '5%',
                    'soporte' => '24/7 Prioritario',
                    'caracteristicas' => [
                        'Productos ilimitados' => true,
                        'Destacado en búsquedas' => true,
                        'Estadísticas avanzadas' => true,
                        'Soporte prioritario' => true
                    ]
                ]
            ],
            'moneda' => 'Bs.'
        ]);
    }

    public function estado(Request $request)
    {
        $vendedor = $request->user()->vendedor;

        return response()->json([
            'success' => true,
            'membresia_actual' => $vendedor->membresia,
            'fecha_vencimiento' => $vendedor->fecha_vencimiento_membresia,
            'activa' => $vendedor->tieneMembresiaActiva(),
            'dias_restantes' => $vendedor->fecha_vencimiento_membresia?->diffInDays(now(), false) ?? 0,
            'limite_productos' => $vendedor->obtenerLimiteProductos(),
            'productos_publicados' => $vendedor->productos()->count(),
            'comision_actual' => $this->obtenerComisionActual($vendedor->membresia)
        ]);
    }

    public function renovar(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $validator = Validator::make($request->all(), [
            'plan' => ['required', 'in:basico,profesional,premium,ilimitado'],
            'duracion_meses' => ['required', 'integer', 'in:1,3,6,12'],
            'metodo_pago' => ['required', 'in:transferencia_bancaria,pago_movil,efectivo'],
            'referencia_pago' => ['required_if:metodo_pago,transferencia_bancaria,pago_movil', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $planes = [
            'basico' => 0,
            'profesional' => 25000,
            'premium' => 45000,
            'ilimitado' => 75000
        ];

        $monto = $planes[$request->plan] * $request->duracion_meses;

        DB::beginTransaction();
        try {
            // Crear registro de pago pendiente en comisiones o tabla de pagos
            $vendedor->membresia = $request->plan;
            $vendedor->fecha_vencimiento_membresia = $vendedor->tieneMembresiaActiva()
                ? $vendedor->fecha_vencimiento_membresia->addMonths($request->duracion_meses)
                : now()->addMonths($request->duracion_meses);

            $vendedor->save();

            // Registrar en bitácora
            \App\Models\BitacoraSistema::registrar(
                $user->id,
                'renovar_membresia',
                'Vendedor',
                $vendedor->id,
                "Membresía renovada: {$request->plan} por {$request->duracion_meses} meses",
                null,
                [
                    'plan' => $request->plan,
                    'duracion_meses' => $request->duracion_meses,
                    'monto' => $monto,
                    'metodo_pago' => $request->metodo_pago
                ]
            );

            // Notificación
            $user->generarNotificacion(
                'Membresía renovada',
                "Tu plan {$request->plan} ha sido renovado exitosamente por {$request->duracion_meses} meses.",
                'vendedor'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Membresía renovada exitosamente',
                'vendedor' => $vendedor->fresh(),
                'monto_pagado' => $monto,
                'plan' => $request->plan,
                'vence_en' => $vendedor->fecha_vencimiento_membresia->format('d/m/Y')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar membresía: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerComisionActual($plan)
    {
        $comisiones = [
            'basico' => 15,
            'profesional' => 12,
            'premium' => 8,
            'ilimitado' => 5
        ];
        return $comisiones[$plan] ?? 15;
    }
}