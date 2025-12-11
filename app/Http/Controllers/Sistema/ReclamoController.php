<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Reclamo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReclamoController extends Controller
{
    /**
     * Listar reclamos del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = $user->reclamos()->with(['pedido', 'asignadoA', 'productoReemplazo']);
        
        // Filtrar por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }
        
        // Filtrar por tipo
        if ($request->has('tipo')) {
            $query->where('tipo_reclamo', $request->tipo);
        }
        
        // Filtrar por prioridad
        if ($request->has('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }
        
        // Buscar por código
        if ($request->has('search')) {
            $query->where('codigo_reclamo', 'LIKE', "%{$request->search}%")
                  ->orWhereHas('pedido', function($q) use ($request) {
                      $q->where('numero_pedido', 'LIKE', "%{$request->search}%");
                  });
        }
        
        // Ordenar
        $orden = $request->get('orden', 'recientes');
        if ($orden === 'recientes') {
            $query->orderBy('created_at', 'desc');
        } elseif ($orden === 'antiguos') {
            $query->orderBy('created_at', 'asc');
        } elseif ($orden === 'prioridad') {
            $query->orderBy('prioridad')->orderBy('created_at', 'desc');
        }
        
        $reclamos = $query->paginate($request->get('per_page', 15));
        
        // Estadísticas
        $estadisticas = [
            'total' => $user->reclamos()->count(),
            'abiertos' => $user->reclamos()->where('estado', 'abierto')->count(),
            'en_revision' => $user->reclamos()->where('estado', 'en_revision')->count(),
            'resueltos' => $user->reclamos()->where('estado', 'resuelto')->count(),
            'cerrados' => $user->reclamos()->where('estado', 'cerrado')->count(),
            'por_tipo' => $user->reclamos()
                ->selectRaw('tipo_reclamo, COUNT(*) as cantidad')
                ->groupBy('tipo_reclamo')
                ->get(),
        ];
        
        return response()->json([
            'success' => true,
            'reclamos' => $reclamos,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Mostrar detalle de reclamo
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $reclamo = $user->reclamos()
            ->with([
                'pedido.vendedor',
                'pedido.items.producto',
                'asignadoA',
                'productoReemplazo',
                'seguimientos.user'
            ])
            ->findOrFail($id);
        
        // Estadísticas del reclamo
        $estadisticas = $reclamo->obtenerEstadisticas();
        
        return response()->json([
            'success' => true,
            'reclamo' => $reclamo,
            'estadisticas' => $estadisticas,
            'puede_aceptar_resolucion' => $reclamo->estado === 'resuelto' && !$reclamo->resolucion_aceptada,
            'puede_rechazar_resolucion' => $reclamo->estado === 'resuelto' && !$reclamo->resolucion_aceptada,
            'vencimiento' => $reclamo->fecha_limite_respuesta?->format('d/m/Y H:i'),
            'dias_restantes' => $reclamo->fecha_limite_respuesta?->diffInDays(now(), false) * -1,
        ]);
    }

    /**
     * Crear nuevo reclamo
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido_id' => ['required', 'exists:pedidos,id'],
            'tipo_reclamo' => ['required', 'in:producto_defectuoso,producto_incorrecto,no_recibido,tardio,garantia,otro'],
            'descripcion' => ['required', 'string', 'min:20'],
            'categoria_reclamo' => ['nullable', 'string'],
            'reembolso_solicitado' => ['boolean'],
            'monto_reembolso' => ['nullable', 'numeric', 'min:0'],
            'producto_reemplazo_id' => ['nullable', 'exists:productos,id'],
            'evidencias' => ['nullable', 'array'],
            'evidencias.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($request->pedido_id);

        // Verificar que el pedido sea elegible para reclamo
        if (!$this->pedidoElegibleParaReclamo($pedido)) {
            return response()->json([
                'success' => false,
                'message' => 'Este pedido no es elegible para reclamo'
            ], 400);
        }

        // Verificar que no exista un reclamo activo para este pedido
        $reclamoExistente = Reclamo::where('pedido_id', $pedido->id)
            ->whereIn('estado', ['abierto', 'en_revision'])
            ->exists();
            
        if ($reclamoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un reclamo activo para este pedido'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Verificar producto de reemplazo
            $productoReemplazo = null;
            if ($request->producto_reemplazo_id) {
                $productoReemplazo = Producto::find($request->producto_reemplazo_id);
                if (!$productoReemplazo || $productoReemplazo->vendedor_id !== $pedido->vendedor_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El producto de reemplazo no es válido'
                    ], 400);
                }
            }

            // Subir evidencias
            $evidenciasPaths = [];
            if ($request->hasFile('evidencias')) {
                foreach ($request->file('evidencias') as $evidencia) {
                    $path = $evidencia->store('reclamos/evidencias', 'public');
                    $evidenciasPaths[] = [
                        'path' => $path,
                        'nombre' => $evidencia->getClientOriginalName(),
                        'tipo' => $evidencia->getMimeType(),
                        'tamaño' => $evidencia->getSize(),
                    ];
                }
            }

            // Crear reclamo
            $reclamo = Reclamo::create([
                'pedido_id' => $pedido->id,
                'user_id' => $user->id,
                'tipo_reclamo' => $request->tipo_reclamo,
                'descripcion' => $request->descripcion,
                'categoria_reclamo' => $request->categoria_reclamo,
                'reembolso_solicitado' => $request->reembolso_solicitado ?? false,
                'monto_reembolso' => $request->monto_reembolso,
                'producto_reemplazo_id' => $request->producto_reemplazo_id,
                'evidencias' => $evidenciasPaths,
                'estado' => 'abierto',
                'prioridad' => $this->calcularPrioridadReclamo($request->tipo_reclamo, $user),
            ]);

            // Agregar primer seguimiento
            $reclamo->agregarSeguimiento(
                "Reclamo creado: {$request->descripcion}",
                $user->id,
                'creacion'
            );

            // Actualizar estado del pedido si es necesario
            if (in_array($request->tipo_reclamo, ['producto_defectuoso', 'producto_incorrecto', 'no_recibido'])) {
                $pedido->estado_pedido = 'reclamado';
                $pedido->comentario_reclamo = "Reclamo {$reclamo->codigo_reclamo}: {$request->tipo_reclamo}";
                $pedido->save();
            }

            // Registrar en bitácora
            BitacoraSistema::registrarCreacion(
                $user->id,
                'Reclamo',
                $reclamo->id,
                $reclamo->toArray()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reclamo creado exitosamente',
                'reclamo' => $reclamo->load(['pedido', 'pedido.vendedor']),
                'codigo_reclamo' => $reclamo->codigo_reclamo,
                'fecha_limite_respuesta' => $reclamo->fecha_limite_respuesta->format('d/m/Y H:i'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear reclamo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar seguimiento al reclamo
     */
    public function addSeguimiento(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => ['required', 'string', 'min:10'],
            'tipo' => ['nullable', 'in:comentario,evidencia,respuesta,actualizacion'],
            'evidencias' => ['nullable', 'array'],
            'evidencias.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $reclamo = $user->reclamos()->findOrFail($id);

        // Verificar que el reclamo esté activo
        if (!in_array($reclamo->estado, ['abierto', 'en_revision'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede agregar seguimiento a un reclamo cerrado'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Subir evidencias si existen
            $evidenciasPaths = [];
            if ($request->hasFile('evidencias')) {
                foreach ($request->file('evidencias') as $evidencia) {
                    $path = $evidencia->store('reclamos/seguimientos/' . $reclamo->id, 'public');
                    $evidenciasPaths[] = [
                        'path' => $path,
                        'nombre' => $evidencia->getClientOriginalName(),
                        'tipo' => $evidencia->getMimeType(),
                    ];
                }
            }

            // Agregar seguimiento
            $seguimiento = $reclamo->agregarSeguimiento(
                $request->descripcion . (count($evidenciasPaths) > 0 ? ' [Con evidencias adjuntas]' : ''),
                $user->id,
                $request->tipo ?? 'comentario'
            );

            // Adjuntar evidencias al seguimiento si existen
            if (count($evidenciasPaths) > 0) {
                $seguimiento->evidencias = $evidenciasPaths;
                $seguimiento->save();
            }

            // Notificar al vendedor/administrador según quién agregó el seguimiento
            if ($user->id === $reclamo->user_id) {
                // Cliente agregó seguimiento, notificar al vendedor
                $reclamo->pedido->vendedor->user->generarNotificacion(
                    'Nuevo seguimiento en reclamo',
                    "El cliente ha agregado un seguimiento al reclamo {$reclamo->codigo_reclamo}",
                    'reclamo',
                    ['reclamo_id' => $reclamo->id]
                );
            } else {
                // Vendedor/Admin agregó seguimiento, notificar al cliente
                $reclamo->user->generarNotificacion(
                    'Nuevo seguimiento en tu reclamo',
                    "Se ha agregado un seguimiento a tu reclamo {$reclamo->codigo_reclamo}",
                    'reclamo',
                    ['reclamo_id' => $reclamo->id]
                );
            }

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'agregar_seguimiento_reclamo',
                'Reclamo',
                $reclamo->id,
                "Seguimiento agregado: {$request->descripcion}",
                null,
                ['tipo' => $request->tipo, 'con_evidencias' => count($evidenciasPaths)]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento agregado exitosamente',
                'seguimiento' => $seguimiento,
                'reclamo' => $reclamo->fresh(['seguimientos']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar seguimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aceptar resolución del reclamo
     */
    public function aceptarResolucion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comentario' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $reclamo = $user->reclamos()->findOrFail($id);

        // Verificar que el reclamo esté resuelto
        if ($reclamo->estado !== 'resuelto') {
            return response()->json([
                'success' => false,
                'message' => 'El reclamo no está resuelto'
            ], 400);
        }

        // Verificar que no haya sido ya aceptado
        if ($reclamo->resolucion_aceptada) {
            return response()->json([
                'success' => false,
                'message' => 'La resolución ya fue aceptada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $reclamo->aceptarResolucion($request->comentario);

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'aceptar_resolucion_reclamo',
                'Reclamo',
                $reclamo->id,
                'Resolución aceptada por el cliente',
                null,
                ['comentario' => $request->comentario]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resolución aceptada exitosamente',
                'reclamo' => $reclamo->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar resolución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar resolución del reclamo
     */
    public function rechazarResolucion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comentario' => ['required', 'string', 'min:10'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $reclamo = $user->reclamos()->findOrFail($id);

        // Verificar que el reclamo esté resuelto
        if ($reclamo->estado !== 'resuelto') {
            return response()->json([
                'success' => false,
                'message' => 'El reclamo no está resuelto'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $reclamo->rechazarResolucion($request->comentario);

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'rechazar_resolucion_reclamo',
                'Reclamo',
                $reclamo->id,
                "Resolución rechazada por el cliente: {$request->comentario}",
                null,
                ['comentario' => $request->comentario]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resolución rechazada',
                'reclamo' => $reclamo->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar resolución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar elegibilidad de pedido para reclamo
     */
    private function pedidoElegibleParaReclamo($pedido)
    {
        // Verificar que el pedido esté entregado o en estado reclamable
        if (!in_array($pedido->estado_pedido, ['entregado', 'reclamado'])) {
            return false;
        }

        // Verificar que no hayan pasado más de X días desde la entrega (30 días para garantía)
        $diasMaximos = 30;
        if ($pedido->fecha_entrega && $pedido->fecha_entrega->diffInDays(now()) > $diasMaximos) {
            return false;
        }

        // Verificar que no tenga reclamos resueltos recientemente
        $reclamosResueltos = $pedido->reclamos()
            ->whereIn('estado', ['resuelto', 'cerrado'])
            ->where('created_at', '>', now()->subDays($diasMaximos))
            ->exists();
            
        if ($reclamosResueltos) {
            return false;
        }

        return true;
    }

    /**
     * Calcular prioridad del reclamo
     */
    private function calcularPrioridadReclamo($tipoReclamo, $user)
    {
        $prioridad = 3; // Normal
        
        $prioridades = [
            'producto_defectuoso' => 1,
            'no_recibido' => 1,
            'producto_incorrecto' => 2,
            'tardio' => 2,
            'garantia' => 2,
            'otro' => 3,
        ];
        
        if (isset($prioridades[$tipoReclamo])) {
            $prioridad = $prioridades[$tipoReclamo];
        }
        
        // Aumentar prioridad si es cliente frecuente
        if ($user->total_compras > 10) {
            $prioridad = max(1, $prioridad - 1);
        }
        
        return $prioridad;
    }

    /**
     * Obtener tipos de reclamo disponibles
     */
    public function getTiposReclamo()
    {
        $tipos = [
            [
                'codigo' => 'producto_defectuoso',
                'nombre' => 'Producto Defectuoso',
                'descripcion' => 'El producto presenta fallas o no funciona correctamente',
                'tiempo_respuesta' => '24-48 horas',
                'requiere_evidencias' => true,
            ],
            [
                'codigo' => 'producto_incorrecto',
                'nombre' => 'Producto Incorrecto',
                'descripcion' => 'Recibí un producto diferente al que pedí',
                'tiempo_respuesta' => '24-48 horas',
                'requiere_evidencias' => true,
            ],
            [
                'codigo' => 'no_recibido',
                'nombre' => 'No Recibí el Producto',
                'descripcion' => 'El pedido marca como entregado pero no lo recibí',
                'tiempo_respuesta' => '24-72 horas',
                'requiere_evidencias' => false,
            ],
            [
                'codigo' => 'tardio',
                'nombre' => 'Entrega Tardía',
                'descripcion' => 'El pedido se entregó fuera del tiempo estimado',
                'tiempo_respuesta' => '48-72 horas',
                'requiere_evidencias' => false,
            ],
            [
                'codigo' => 'garantia',
                'nombre' => 'Garantía',
                'descripcion' => 'Ejecución de garantía del producto',
                'tiempo_respuesta' => '3-5 días',
                'requiere_evidencias' => true,
            ],
            [
                'codigo' => 'otro',
                'nombre' => 'Otro',
                'descripcion' => 'Otro tipo de reclamo no listado',
                'tiempo_respuesta' => '48-72 horas',
                'requiere_evidencias' => false,
            ],
        ];
        
        return response()->json([
            'success' => true,
            'tipos' => $tipos,
        ]);
    }

    /**
     * Obtener pedidos elegibles para reclamo
     */
    public function getPedidosElegibles(Request $request)
    {
        $user = $request->user();
        
        $pedidos = $user->pedidos()
            ->where('estado_pedido', 'entregado')
            ->where('created_at', '>=', now()->subDays(30)) // Últimos 30 días
            ->with(['vendedor', 'items.producto'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($pedido) {
                return $this->pedidoElegibleParaReclamo($pedido);
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'pedidos' => $pedidos,
            'total' => $pedidos->count(),
        ]);
    }

    /**
     * Descargar evidencias del reclamo
     */
    public function downloadEvidencias(Request $request, $id)
    {
        $user = $request->user();
        $reclamo = $user->reclamos()->findOrFail($id);
        
        if (empty($reclamo->evidencias)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay evidencias para descargar'
            ], 404);
        }
        
        // En un entorno real, aquí se comprimirían los archivos y se devolvería el ZIP
        $archivos = collect($reclamo->evidencias)->map(function($evidencia) {
            return [
                'nombre' => $evidencia['nombre'] ?? 'evidencia',
                'url' => Storage::url($evidencia['path']),
                'tamaño' => $evidencia['tamaño'] ?? 'Desconocido',
                'tipo' => $evidencia['tipo'] ?? 'application/octet-stream',
            ];
        });
        
        return response()->json([
            'success' => true,
            'archivos' => $archivos,
            'total_archivos' => count($reclamo->evidencias),
            'tamaño_total' => collect($reclamo->evidencias)->sum('tamaño') ?? 0,
        ]);
    }

    /**
     * Solicitar reembolso directo
     */
    public function solicitarReembolso(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'monto' => ['required', 'numeric', 'min:0'],
            'motivo' => ['required', 'string', 'min:10'],
            'cuenta_bancaria' => ['nullable', 'string'],
            'banco' => ['nullable', 'string'],
            'tipo_cuenta' => ['nullable', 'in:ahorro,corriente'],
            'cedula_titular' => ['nullable', 'string'],
            'nombre_titular' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $reclamo = $user->reclamos()->findOrFail($id);

        // Verificar que el reclamo esté en estado apropiado
        if (!in_array($reclamo->estado, ['abierto', 'en_revision', 'resuelto'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede solicitar reembolso en el estado actual del reclamo'
            ], 400);
        }

        // Verificar que el monto no exceda el total del pedido
        if ($request->monto > $reclamo->pedido->total) {
            return response()->json([
                'success' => false,
                'message' => 'El monto solicitado excede el total del pedido'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $reclamo->reembolso_solicitado = true;
            $reclamo->monto_reembolso = $request->monto;
            $reclamo->save();

            // Agregar seguimiento
            $reclamo->agregarSeguimiento(
                "Solicitud de reembolso por Bs. " . number_format($request->monto, 2, ',', '.') . 
                ". Motivo: {$request->motivo}",
                $user->id,
                'reembolso'
            );

            // Notificar a administradores
            \App\Models\User::where('tipo_usuario', 'administrador')
                ->get()
                ->each(function($admin) use ($reclamo, $request) {
                    $admin->generarNotificacion(
                        'Solicitud de reembolso',
                        "El cliente ha solicitado un reembolso de Bs. " . 
                        number_format($request->monto, 2, ',', '.') . 
                        " para el reclamo {$reclamo->codigo_reclamo}",
                        'reclamo',
                        ['reclamo_id' => $reclamo->id, 'monto' => $request->monto]
                    );
                });

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'solicitar_reembolso',
                'Reclamo',
                $reclamo->id,
                "Solicitud de reembolso por Bs. {$request->monto}",
                null,
                [
                    'monto' => $request->monto,
                    'motivo' => $request->motivo,
                    'cuenta_bancaria' => $request->cuenta_bancaria,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de reembolso enviada exitosamente',
                'reclamo' => $reclamo->fresh(),
                'tiempo_procesamiento' => '3-5 días hábiles',
                'informacion_adicional' => [
                    'monto_solicitado' => $request->monto,
                    'total_pedido' => $reclamo->pedido->total,
                    'porcentaje' => ($request->monto / $reclamo->pedido->total) * 100,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al solicitar reembolso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar reclamo
     */
    public function cancelar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'motivo' => ['required', 'string', 'min:10'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $reclamo = $user->reclamos()->findOrFail($id);

        // Verificar que el reclamo esté activo
        if (!in_array($reclamo->estado, ['abierto', 'en_revision'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar un reclamo cerrado o resuelto'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $reclamo->estado = 'cerrado';
            $reclamo->solucion = "Reclamo cancelado por el cliente. Motivo: {$request->motivo}";
            $reclamo->fecha_resolucion = now();
            $reclamo->save();

            // Agregar seguimiento
            $reclamo->agregarSeguimiento(
                "Reclamo cancelado por el cliente. Motivo: {$request->motivo}",
                $user->id,
                'cancelacion'
            );

            // Actualizar estado del pedido si estaba en reclamado
            if ($reclamo->pedido->estado_pedido === 'reclamado') {
                $reclamo->pedido->estado_pedido = 'entregado';
                $reclamo->pedido->save();
            }

            // Notificar al vendedor
            $reclamo->pedido->vendedor->user->generarNotificacion(
                'Reclamo cancelado',
                "El cliente ha cancelado el reclamo {$reclamo->codigo_reclamo}",
                'reclamo',
                ['reclamo_id' => $reclamo->id]
            );

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'cancelar_reclamo',
                'Reclamo',
                $reclamo->id,
                "Reclamo cancelado por el cliente: {$request->motivo}",
                ['estado' => $reclamo->getOriginal('estado')],
                ['estado' => 'cerrado', 'motivo' => $request->motivo]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reclamo cancelado exitosamente',
                'reclamo' => $reclamo->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar reclamo: ' . $e->getMessage()
            ], 500);
        }
    }
}