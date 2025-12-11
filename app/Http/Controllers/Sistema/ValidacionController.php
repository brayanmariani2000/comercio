<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Validacion;
use App\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ValidacionController extends Controller
{
    /**
     * Validar compra mediante QR o Serial
     */
    public function validarCompra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => ['required_without:serial', 'string'],
            'serial' => ['required_without:codigo', 'string'],
            'tipo_validacion' => ['nullable', 'in:qr,serial,ambos'],
            'dispositivo' => ['nullable', 'string'],
            'ubicacion' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pedido = null;
            $resultado = null;
            $tipo = 'invalido';

            // Buscar por código QR
            if ($request->filled('codigo')) {
                $pedido = Pedido::where('codigo_qr', $request->codigo)->first();
                if ($pedido) {
                    $tipo = 'qr';
                }
            }

            // Buscar por serial si no se encontró por QR
            if (!$pedido && $request->filled('serial')) {
                $pedido = Pedido::where('serial_compra', $request->serial)->first();
                if ($pedido) {
                    $tipo = 'serial';
                }
            }

            if ($pedido) {
                // Validar la compra
                $resultado = $pedido->validarCompra($request->codigo, $request->serial);
                
                // Registrar validación exitosa en bitácora
                BitacoraSistema::registrar(
                    Auth::id() ?? $pedido->user_id,
                    'validacion_exitosa',
                    'Pedido',
                    $pedido->id,
                    "Validación {$tipo} exitosa para pedido {$pedido->numero_pedido}",
                    null,
                    [
                        'tipo' => $tipo,
                        'codigo' => $request->codigo,
                        'serial' => $request->serial,
                        'dispositivo' => $request->dispositivo,
                        'ubicacion' => $request->ubicacion,
                    ]
                );

            } else {
                // Registrar intento fallido en bitácora
                BitacoraSistema::registrar(
                    Auth::id() ?? null,
                    'validacion_fallida',
                    null,
                    null,
                    "Intento de validación fallido",
                    null,
                    [
                        'codigo' => $request->codigo,
                        'serial' => $request->serial,
                        'dispositivo' => $request->dispositivo,
                        'ubicacion' => $request->ubicacion,
                    ]
                );
            }

            DB::commit();

            if ($pedido) {
                return response()->json([
                    'success' => true,
                    'valido' => true,
                    'tipo' => $tipo,
                    'pedido' => [
                        'id' => $pedido->id,
                        'numero_pedido' => $pedido->numero_pedido,
                        'fecha' => $pedido->created_at->format('d/m/Y H:i'),
                        'cliente' => [
                            'nombre' => $pedido->nombre_cliente,
                            'cedula' => $pedido->cedula_cliente,
                        ],
                        'vendedor' => [
                            'nombre' => $pedido->vendedor->nombre_comercial,
                            'rif' => $pedido->vendedor->rif,
                        ],
                        'total' => $pedido->total,
                        'estado' => $pedido->estado_pedido,
                        'estado_pago' => $pedido->estado_pago,
                    ],
                    'validacion_id' => $resultado['validacion_id'] ?? null,
                    'qr_image' => $pedido->generarQRImagen(150),
                    'mensaje' => '✅ COMPRA VÁLIDA - Producto original',
                    'recomendaciones' => $this->getRecomendacionesValidacion($pedido),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'valido' => false,
                    'mensaje' => '❌ CÓDIGO INVÁLIDO - Este producto podría no ser original',
                    'alerta' => 'Se ha registrado un intento de validación con código inválido',
                    'recomendaciones' => [
                        'Verifica que el código esté correctamente escaneado',
                        'Confirma que el producto sea de un vendedor verificado',
                        'Reporta este intento si sospechas de fraude',
                    ]
                ], 404);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error en la validación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar compra para reclamo (vista de administrador/vendedor)
     */
    public function validarParaReclamo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_qr' => ['required_without:serial_compra', 'string'],
            'serial_compra' => ['required_without:codigo_qr', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Solo administradores, supervisores o el vendedor dueño pueden validar para reclamo
        $pedido = Pedido::where('codigo_qr', $request->codigo_qr)
            ->orWhere('serial_compra', $request->serial_compra)
            ->first();

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'valido' => false,
                'mensaje' => 'Código no encontrado en el sistema'
            ], 404);
        }

        // Verificar permisos
        if (!$user->esAdministrador() && !$user->esSupervisor() && 
            $pedido->vendedor->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para validar este pedido'
            ], 403);
        }

        $validacion = $pedido->validarCompra($request->codigo_qr, $request->serial_compra);

        return response()->json([
            'success' => true,
            'valido' => $validacion['valido'],
            'pedido' => $validacion['valido'] ? $pedido->load(['user', 'items.producto', 'reclamos']) : null,
            'validaciones_previas' => $pedido->validaciones()->orderBy('created_at', 'desc')->get(),
            'reclamos_asociados' => $pedido->reclamos()->get(),
            'informacion_completa' => $pedido->datosParaImpresion(),
        ]);
    }

    /**
     * Obtener historial de validaciones de un pedido
     */
    public function historialValidaciones(Request $request, $pedidoId)
    {
        $user = $request->user();
        $pedido = Pedido::findOrFail($pedidoId);

        // Verificar permisos
        if (!$user->esAdministrador() && !$user->esSupervisor() && 
            $pedido->vendedor->user_id !== $user->id && 
            $pedido->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver este historial'
            ], 403);
        }

        $validaciones = $pedido->validaciones()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'pedido' => $pedido->only(['id', 'numero_pedido', 'codigo_qr', 'serial_compra']),
            'validaciones' => $validaciones,
            'total_validaciones' => $pedido->validaciones()->count(),
            'validaciones_exitosas' => $pedido->validaciones()->where('valido', true)->count(),
            'validaciones_fallidas' => $pedido->validaciones()->where('valido', false)->count(),
            'ultima_validacion' => $pedido->validaciones()->latest()->first(),
        ]);
    }

    /**
     * Generar nuevo QR para un pedido (solo admin/vendedor dueño)
     */
    public function regenerarQR(Request $request, $pedidoId)
    {
        $user = $request->user();
        $pedido = Pedido::findOrFail($pedidoId);

        // Verificar permisos
        if (!$user->esAdministrador() && !$user->esSupervisor() && 
            $pedido->vendedor->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para regenerar el QR'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $codigoQRAnterior = $pedido->codigo_qr;
            
            // Generar nuevo código QR
            $pedido->codigo_qr = Pedido::generarCodigoQR();
            $pedido->save();

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'regenerar_qr',
                'Pedido',
                $pedido->id,
                'QR regenerado para el pedido',
                ['codigo_qr_anterior' => $codigoQRAnterior],
                ['codigo_qr_nuevo' => $pedido->codigo_qr]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'QR regenerado exitosamente',
                'nuevo_codigo_qr' => $pedido->codigo_qr,
                'qr_image' => $pedido->generarQRImagen(200),
                'pedido' => $pedido->only(['id', 'numero_pedido', 'serial_compra']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar reporte de validaciones
     */
    public function reporteValidaciones(Request $request)
    {
        // Solo administradores y supervisores
        $user = $request->user();
        if (!$user->esAdministrador() && !$user->esSupervisor()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver este reporte'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date'],
            'vendedor_id' => ['nullable', 'exists:vendedores,id'],
            'tipo_validacion' => ['nullable', 'in:qr,serial'],
            'valido' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Validacion::with(['pedido.vendedor', 'pedido.user'])
            ->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->has('vendedor_id')) {
            $query->whereHas('pedido', function($q) use ($request) {
                $q->where('vendedor_id', $request->vendedor_id);
            });
        }

        if ($request->has('tipo_validacion')) {
            $query->where('tipo_validacion', $request->tipo_validacion);
        }

        if ($request->has('valido')) {
            $query->where('valido', $request->valido);
        }

        $validaciones = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 50));

        // Estadísticas
        $estadisticas = [
            'total' => $validaciones->total(),
            'validas' => Validacion::whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin])
                ->where('valido', true)->count(),
            'invalidas' => Validacion::whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin])
                ->where('valido', false)->count(),
            'por_tipo' => Validacion::whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin])
                ->selectRaw('tipo_validacion, COUNT(*) as cantidad')
                ->groupBy('tipo_validacion')
                ->get(),
            'por_vendedor' => Validacion::whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin])
                ->selectRaw('pedidos.vendedor_id, COUNT(*) as cantidad')
                ->join('pedidos', 'validaciones.pedido_id', '=', 'pedidos.id')
                ->groupBy('pedidos.vendedor_id')
                ->with('pedido.vendedor')
                ->orderBy('cantidad', 'desc')
                ->limit(10)
                ->get(),
            'por_dia' => Validacion::whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin])
                ->selectRaw('DATE(created_at) as fecha, COUNT(*) as cantidad')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'validaciones' => $validaciones,
            'estadisticas' => $estadisticas,
            'parametros' => $request->all(),
        ]);
    }

    /**
     * Escanear QR desde app móvil (API para apps)
     */
    public function scanQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => ['required', 'string'],
            'device_id' => ['required', 'string'],
            'location' => ['nullable', 'string'],
            'app_version' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Decodificar datos del QR
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['codigo_qr'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR inválido'
                ], 400);
            }

            $pedido = Pedido::where('codigo_qr', $qrData['codigo_qr'])->first();

            if (!$pedido) {
                // Registrar intento fallido
                BitacoraSistema::registrar(
                    null,
                    'scan_qr_fallido',
                    null,
                    null,
                    'Intento de escaneo QR fallido desde app móvil',
                    null,
                    [
                        'device_id' => $request->device_id,
                        'location' => $request->location,
                        'qr_data' => substr($request->qr_data, 0, 100),
                    ]
                );

                return response()->json([
                    'success' => false,
                    'valido' => false,
                    'mensaje' => 'QR no encontrado en el sistema',
                    'codigo_error' => 'QR_NOT_FOUND'
                ], 404);
            }

            // Validar la compra
            $validacion = $pedido->validarCompra($qrData['codigo_qr'], null);

            // Preparar respuesta para app móvil
            $respuesta = [
                'success' => true,
                'valido' => $validacion['valido'],
                'pedido' => [
                    'id' => $pedido->id,
                    'numero' => $pedido->numero_pedido,
                    'fecha' => $pedido->created_at->format('Y-m-d H:i:s'),
                    'cliente' => [
                        'nombre' => $pedido->nombre_cliente,
                        'cedula' => $pedido->cedula_cliente,
                    ],
                    'vendedor' => [
                        'nombre' => $pedido->vendedor->nombre_comercial,
                        'rif' => $pedido->vendedor->rif,
                    ],
                    'total' => $pedido->total,
                    'estado' => $pedido->estado_pedido,
                    'items' => $pedido->items->map(function($item) {
                        return [
                            'producto' => $item->producto->nombre,
                            'cantidad' => $item->cantidad,
                            'precio' => $item->precio_unitario,
                        ];
                    }),
                ],
                'validacion' => [
                    'id' => $validacion['validacion_id'],
                    'fecha' => now()->format('Y-m-d H:i:s'),
                    'dispositivo' => $request->device_id,
                ],
                'qr_image' => $pedido->generarQRImagen(100),
                'acciones' => $this->getAccionesApp($pedido),
            ];

            return response()->json($respuesta);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar QR: ' . $e->getMessage(),
                'codigo_error' => 'PROCESSING_ERROR'
            ], 500);
        }
    }

    /**
     * Verificar autenticidad de producto en tienda física
     */
    public function verificarTiendaFisica(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial' => ['required', 'string'],
            'tienda_id' => ['required', 'exists:tiendas_fisicas,id'],
            'empleado_id' => ['required', 'exists:empleados,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pedido = Pedido::where('serial_compra', $request->serial)->first();

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'valido' => false,
                'mensaje' => 'Serial no registrado en el sistema',
                'alerta' => 'PRODUCTO POTENCIALMENTE FALSIFICADO'
            ], 404);
        }

        $validacion = $pedido->validarCompra(null, $request->serial);

        // Registrar validación desde tienda física
        $validacionRecord = $pedido->validaciones()->create([
            'codigo_qr' => null,
            'serial_compra' => $request->serial,
            'tipo_validacion' => 'serial_tienda',
            'dispositivo' => 'Sistema Tienda Física',
            'ubicacion' => 'Tienda ID: ' . $request->tienda_id,
            'resultado' => $validacion['valido'] ? 'Válido - Verificado en tienda física' : 'Inválido',
            'valido' => $validacion['valido'],
        ]);

        return response()->json([
            'success' => true,
            'valido' => $validacion['valido'],
            'validacion_id' => $validacionRecord->id,
            'pedido' => $pedido->only(['id', 'numero_pedido', 'fecha_compra', 'cliente', 'vendedor']),
            'tienda' => [
                'id' => $request->tienda_id,
                'empleado' => $request->empleado_id,
                'fecha_verificacion' => now()->format('Y-m-d H:i:s'),
            ],
            'comprobante' => [
                'numero' => 'VER-' . strtoupper(uniqid()),
                'fecha' => now()->format('d/m/Y'),
                'hora' => now()->format('H:i:s'),
                'tienda' => 'Tienda Física #' . $request->tienda_id,
            ]
        ]);
    }

    /**
     * Obtener recomendaciones según el estado del pedido
     */
    private function getRecomendacionesValidacion($pedido)
    {
        $recomendaciones = [];

        if ($pedido->estado_pedido === 'pendiente') {
            $recomendaciones[] = '⚠️ El pedido está pendiente de pago';
            $recomendaciones[] = 'Espera la confirmación del pago antes de usar el producto';
        } elseif ($pedido->estado_pedido === 'cancelado') {
            $recomendaciones[] = '❌ Este pedido fue cancelado';
            $recomendaciones[] = 'No aceptes productos de pedidos cancelados';
        } elseif ($pedido->estado_pedido === 'entregado') {
            $recomendaciones[] = '✅ Pedido entregado correctamente';
            $recomendaciones[] = 'Producto válido y original';
        }

        // Verificar múltiples validaciones
        $validacionesCount = $pedido->validaciones()->count();
        if ($validacionesCount > 1) {
            $recomendaciones[] = "⚠️ Este código ha sido validado {$validacionesCount} veces";
            $recomendaciones[] = 'Verifica que sea la primera vez que se usa este producto';
        }

        return $recomendaciones;
    }

    /**
     * Obtener acciones disponibles para app móvil
     */
    private function getAccionesApp($pedido)
    {
        $acciones = [];

        if ($pedido->estado_pedido === 'entregado') {
            $acciones[] = [
                'accion' => 'ver_detalles',
                'titulo' => 'Ver detalles del pedido',
                'url' => '/pedidos/' . $pedido->id,
            ];
            
            $acciones[] = [
                'accion' => 'generar_comprobante',
                'titulo' => 'Generar comprobante',
                'url' => '/pedidos/' . $pedido->id . '/comprobante',
            ];
        }

        if ($pedido->reclamos()->count() > 0) {
            $acciones[] = [
                'accion' => 'ver_reclamos',
                'titulo' => 'Ver reclamos asociados',
                'url' => '/pedidos/' . $pedido->id . '/reclamos',
            ];
        }

        $acciones[] = [
            'accion' => 'reportar_problema',
            'titulo' => 'Reportar problema',
            'url' => '/reportar/' . $pedido->id,
        ];

        return $acciones;
    }

    /**
     * API para validación rápida (sin autenticación para kioskos)
     */
    public function validacionRapida(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => ['required', 'string'],
            'kiosko_id' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar kiosko (implementar lógica de kioskos)
        $kioskoValido = $this->validarKiosko($request->kiosko_id);
        
        if (!$kioskoValido) {
            return response()->json([
                'success' => false,
                'message' => 'Kiosko no autorizado'
            ], 403);
        }

        $pedido = Pedido::where('codigo_qr', $request->codigo)
            ->orWhere('serial_compra', $request->codigo)
            ->first();

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'valido' => false,
                'mensaje' => 'Código no encontrado'
            ], 404);
        }

        $validacion = $pedido->validarCompra(
            $pedido->codigo_qr === $request->codigo ? $request->codigo : null,
            $pedido->serial_compra === $request->codigo ? $request->codigo : null
        );

        return response()->json([
            'success' => true,
            'valido' => $validacion['valido'],
            'pedido_numero' => $pedido->numero_pedido,
            'fecha' => $pedido->created_at->format('d/m/Y'),
            'cliente' => substr($pedido->nombre_cliente, 0, 3) . '***', // Ocultar datos sensibles
            'estado' => $pedido->estado_pedido,
            'validaciones_totales' => $pedido->validaciones()->count(),
            'kiosko' => $request->kiosko_id,
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Validar kiosko (método de ejemplo)
     */
    private function validarKiosko($kioskoId)
    {
        // Implementar lógica de validación de kioskos
        // Podría ser una tabla en la base de datos con kioskos autorizados
        $kioskosAutorizados = ['KIOSKO-001', 'KIOSKO-002', 'KIOSKO-003'];
        
        return in_array($kioskoId, $kioskosAutorizados);
    }
}