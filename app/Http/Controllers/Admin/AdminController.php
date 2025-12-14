<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Reclamo;
use App\Models\Cupon;
use App\Models\ConfiguracionTienda;
use App\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard del administrador
     */
    public function dashboard(Request $request)
    {
        // Fechas para estadísticas
        $hoy = now();
        $ayer = now()->subDay();
        $inicioMes = now()->startOfMonth();
        $inicioAnio = now()->startOfYear();
        
        // Estadísticas generales
        $estadisticas = [
            'usuarios' => [
                'total' => User::count(),
                'nuevos_hoy' => User::whereDate('created_at', $hoy)->count(),
                'nuevos_mes' => User::whereMonth('created_at', $hoy->month)->count(),
                'compradores' => User::where('tipo_usuario', 'comprador')->count(),
                'vendedores' => User::where('tipo_usuario', 'vendedor')->count(),
                'activos' => User::where('suspendido', false)->count(),
                'suspendidos' => User::where('suspendido', true)->count(),
            ],
            'vendedores' => [
                'total' => Vendedor::count(),
                'verificados' => Vendedor::where('verificado', true)->count(),
                'pendientes' => Vendedor::where('verificado', false)->count(),
                'activos' => Vendedor::where('activo', true)->count(),
                'inactivos' => Vendedor::where('activo', false)->count(),
                'nuevos_mes' => Vendedor::whereMonth('created_at', $hoy->month)->count(),
            ],
            'productos' => [
                'total' => Producto::count(),
                'aprobados' => Producto::where('aprobado', true)->where('activo', true)->count(),
                'pendientes' => Producto::where('aprobado', false)->count(),
                'activos' => Producto::where('activo', true)->count(),
                'inactivos' => Producto::where('activo', false)->count(),
                'sin_stock' => Producto::where('stock', 0)->count(),
                'stock_bajo' => Producto::whereRaw('stock <= stock_minimo')->where('stock', '>', 0)->count(),
                'nuevos_mes' => Producto::whereMonth('created_at', $hoy->month)->count(),
            ],
            'pedidos' => [
                'total' => Pedido::count(),
                'hoy' => Pedido::whereDate('created_at', $hoy)->count(),
                'mes' => Pedido::whereMonth('created_at', $hoy->month)->count(),
                'pendientes' => Pedido::where('estado_pedido', 'pendiente')->count(),
                'confirmados' => Pedido::where('estado_pedido', 'confirmado')->count(),
                'en_proceso' => Pedido::whereIn('estado_pedido', ['preparando', 'enviado'])->count(),
                'completados' => Pedido::where('estado_pedido', 'entregado')->count(),
                'cancelados' => Pedido::where('estado_pedido', 'cancelado')->count(),
                'valor_total_mes' => Pedido::whereMonth('created_at', $hoy->month)->where('estado_pedido', 'entregado')->sum('total'),
            ],
            'reclamos' => [
                'total' => Reclamo::count(),
                'abiertos' => Reclamo::where('estado', 'abierto')->count(),
                'en_revision' => Reclamo::where('estado', 'en_revision')->count(),
                'resueltos' => Reclamo::where('estado', 'resuelto')->count(),
                'cerrados' => Reclamo::where('estado', 'cerrado')->count(),
                'nuevos_hoy' => Reclamo::whereDate('created_at', $hoy)->count(),
            ],
            'finanzas' => [
                'ventas_hoy' => Pedido::whereDate('created_at', $hoy)->where('estado_pedido', 'entregado')->sum('total'),
                'ventas_mes' => Pedido::whereMonth('created_at', $hoy->month)->where('estado_pedido', 'entregado')->sum('total'),
                'ventas_anio' => Pedido::whereYear('created_at', $hoy->year)->where('estado_pedido', 'entregado')->sum('total'),
                'comisiones_pendientes' => \App\Models\Comision::where('estado', 'pendiente')->sum('monto_comision'),
                'comisiones_pagadas' => \App\Models\Comision::where('estado', 'pagada')->sum('monto_comision'),
                'promedio_ticket' => Pedido::where('estado_pedido', 'entregado')->avg('total'),
            ]
        ];

        // Actividad reciente
        $actividadReciente = BitacoraSistema::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Pedidos recientes
        $pedidosRecientes = Pedido::with(['user', 'vendedor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Reclamos urgentes
        $reclamosUrgentes = Reclamo::with(['user', 'pedido'])
            ->where('prioridad', 1)
            ->whereIn('estado', ['abierto', 'en_revision'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Productos pendientes de aprobación
        $productosPendientes = Producto::with(['vendedor', 'categoria'])
            ->where('aprobado', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Vendedores pendientes de verificación
        $vendedoresPendientes = Vendedor::with('user')
            ->where('verificado', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'estadisticas' => $estadisticas,
            'actividad_reciente' => $actividadReciente,
            'pedidos_recientes' => $pedidosRecientes,
            'reclamos_urgentes' => $reclamosUrgentes,
            'productos_pendientes' => $productosPendientes,
            'vendedores_pendientes' => $vendedoresPendientes,
        ]);
    }

    /**
     * Aprobar producto
     */
    public function approveProduct(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'accion' => ['required', 'in:aprobar,rechazar'],
            'razon' => ['nullable', 'string', 'required_if:accion,rechazar'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $producto = Producto::with('vendedor.user')->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->accion === 'aprobar') {
                $producto->aprobado = true;
                $producto->activo = true;
                $producto->save();

                // Notificar al vendedor
                $producto->vendedor->user->generarNotificacion(
                    'Producto aprobado',
                    "Tu producto '{$producto->nombre}' ha sido aprobado y ya está visible en la tienda",
                    'producto',
                    ['producto_id' => $producto->id]
                );

                $mensaje = 'Producto aprobado exitosamente';

            } else {
                $producto->aprobado = false;
                $producto->activo = false;
                $producto->razon_rechazo = $request->razon;
                $producto->save();

                // Notificar al vendedor
                $producto->vendedor->user->generarNotificacion(
                    'Producto rechazado',
                    "Tu producto '{$producto->nombre}' ha sido rechazado. Razón: {$request->razon}",
                    'producto',
                    ['producto_id' => $producto->id, 'razon' => $request->razon]
                );

                $mensaje = 'Producto rechazado exitosamente';
            }

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                $request->accion === 'aprobar' ? 'aprobar_producto' : 'rechazar_producto',
                'Producto',
                $producto->id,
                $request->accion === 'aprobar' ? 'Producto aprobado' : "Producto rechazado: {$request->razon}",
                ['aprobado' => !$producto->aprobado],
                ['aprobado' => $producto->aprobado, 'razon' => $request->razon]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'producto' => $producto->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar vendedor
     */
    public function verifyVendor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'accion' => ['required', 'in:verificar,rechazar'],
            'razon' => ['nullable', 'string', 'required_if:accion,rechazar'],
            'membresia' => ['nullable', 'in:basico,profesional,premium,ilimitado'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $vendedor = Vendedor::with('user')->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->accion === 'verificar') {
                $vendedor->verificado = true;
                $vendedor->activo = true;
                
                if ($request->membresia) {
                    $vendedor->membresia = $request->membresia;
                }
                
                if ($request->fecha_vencimiento) {
                    $vendedor->fecha_vencimiento_membresia = $request->fecha_vencimiento;
                } else {
                    $vendedor->fecha_vencimiento_membresia = now()->addMonth();
                }
                
                $vendedor->save();

                // Actualizar tipo de usuario si no está ya como vendedor
                if ($vendedor->user->tipo_usuario !== 'vendedor') {
                    $vendedor->user->tipo_usuario = 'vendedor';
                    $vendedor->user->save();
                }

                // Notificar al vendedor
                $vendedor->user->generarNotificacion(
                    'Cuenta de vendedor verificada',
                    "¡Felicidades! Tu cuenta de vendedor ha sido verificada y ya puedes comenzar a publicar productos.",
                    'vendedor',
                    ['vendedor_id' => $vendedor->id]
                );

                $mensaje = 'Vendedor verificado exitosamente';

            } else {
                $vendedor->verificado = false;
                $vendedor->activo = false;
                $vendedor->razon_rechazo = $request->razon;
                $vendedor->save();

                // Notificar al vendedor
                $vendedor->user->generarNotificacion(
                    'Solicitud de vendedor rechazada',
                    "Tu solicitud para ser vendedor ha sido rechazada. Razón: {$request->razon}",
                    'vendedor',
                    ['vendedor_id' => $vendedor->id, 'razon' => $request->razon]
                );

                $mensaje = 'Solicitud de vendedor rechazada';
            }

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                $request->accion === 'verificar' ? 'verificar_vendedor' : 'rechazar_vendedor',
                'Vendedor',
                $vendedor->id,
                $request->accion === 'verificar' ? 'Vendedor verificado' : "Vendedor rechazado: {$request->razon}",
                ['verificado' => !$vendedor->verificado],
                ['verificado' => $vendedor->verificado, 'razon' => $request->razon]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'vendedor' => $vendedor->fresh(['user']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspender/Activar usuario
     */
    public function toggleUserStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'accion' => ['required', 'in:suspender,activar'],
            'razon' => ['nullable', 'string', 'required_if:accion,suspender'],
            'duracion' => ['nullable', 'integer', 'min:1'], // días
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $usuario = User::findOrFail($id);

        // No permitir modificar a otros administradores
        if ($usuario->esAdministrador() && $usuario->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes modificar el estado de otro administrador'
            ], 403);
        }

        DB::beginTransaction();
        try {
            if ($request->accion === 'suspender') {
                $usuario->suspendido = true;
                $usuario->fecha_suspension = now();
                $usuario->razon_suspension = $request->razon;
                
                if ($request->duracion) {
                    $usuario->fecha_reactivacion = now()->addDays($request->duracion);
                }
                
                $usuario->save();

                // Cerrar sesión del usuario si está activo
                $usuario->tokens()->delete();

                $mensaje = 'Usuario suspendido exitosamente';

            } else {
                $usuario->suspendido = false;
                $usuario->fecha_reactivacion = null;
                $usuario->razon_suspension = null;
                $usuario->save();

                $mensaje = 'Usuario activado exitosamente';
            }

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                $request->accion === 'suspender' ? 'suspender_usuario' : 'activar_usuario',
                'User',
                $usuario->id,
                $request->accion === 'suspender' ? "Usuario suspendido: {$request->razon}" : 'Usuario activado',
                ['suspendido' => !$usuario->suspendido],
                ['suspendido' => $usuario->suspendido, 'razon' => $request->razon]
            );

            // Notificar al usuario
            if ($request->accion === 'suspender') {
                $usuario->generarNotificacion(
                    'Cuenta suspendida',
                    "Tu cuenta ha sido suspendida. Razón: {$request->razon}" . 
                    ($request->duracion ? " por {$request->duracion} días" : ""),
                    'seguridad',
                    ['razon' => $request->razon, 'duracion' => $request->duracion]
                );
            } else {
                $usuario->generarNotificacion(
                    'Cuenta reactivada',
                    'Tu cuenta ha sido reactivada. Ya puedes acceder nuevamente.',
                    'seguridad',
                    []
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'usuario' => $usuario->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar configuración de la tienda
     */
    public function updateStoreConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_tienda' => ['nullable', 'string', 'max:255'],
            'rif' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'moneda' => ['nullable', 'string', 'max:10'],
            'simbolo_moneda' => ['nullable', 'string', 'max:5'],
            'iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ciudad' => ['nullable', 'string'],
            'estado' => ['nullable', 'string'],
            'codigo_postal' => ['nullable', 'string'],
            'terminos_condiciones' => ['nullable', 'string'],
            'politica_envios' => ['nullable', 'string'],
            'politica_devoluciones' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $configuracion = ConfiguracionTienda::first();

        if (!$configuracion) {
            $configuracion = new ConfiguracionTienda();
        }

        DB::beginTransaction();
        try {
            $datosAnteriores = $configuracion->toArray();
            
            // Actualizar datos
            $configuracion->fill($request->only([
                'nombre_tienda', 'rif', 'direccion', 'telefono', 'email',
                'moneda', 'simbolo_moneda', 'iva', 'ciudad', 'estado',
                'codigo_postal', 'terminos_condiciones', 'politica_envios',
                'politica_devoluciones'
            ]));

            // Subir logo si existe
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('configuracion', 'public');
                $configuracion->logo = $path;
            }

            // Subir banner si existe
            if ($request->hasFile('banner')) {
                $path = $request->file('banner')->store('configuracion', 'public');
                $configuracion->banner = $path;
            }

            $configuracion->save();
            $datosNuevos = $configuracion->toArray();

            // Registrar en bitácora
            BitacoraSistema::registrarActualizacion(
                $user->id,
                'ConfiguracionTienda',
                $configuracion->id,
                $datosAnteriores,
                $datosNuevos
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'configuracion' => $configuracion,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reportes avanzados
     */
    public function getReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => ['required', 'in:ventas,usuarios,productos,reclamos,finanzas'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date'],
            'agrupacion' => ['nullable', 'in:diaria,semanal,mensual,anual'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $agrupacion = $request->agrupacion ?? 'diaria';

        switch ($request->tipo) {
            case 'ventas':
                $reporte = $this->generarReporteVentas($fechaInicio, $fechaFin, $agrupacion);
                break;
            case 'usuarios':
                $reporte = $this->generarReporteUsuarios($fechaInicio, $fechaFin, $agrupacion);
                break;
            case 'productos':
                $reporte = $this->generarReporteProductos($fechaInicio, $fechaFin, $agrupacion);
                break;
            case 'reclamos':
                $reporte = $this->generarReporteReclamos($fechaInicio, $fechaFin, $agrupacion);
                break;
            case 'finanzas':
                $reporte = $this->generarReporteFinanzas($fechaInicio, $fechaFin, $agrupacion);
                break;
            default:
                $reporte = [];
        }

        return response()->json([
            'success' => true,
            'reporte' => $reporte,
            'parametros' => [
                'tipo' => $request->tipo,
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'agrupacion' => $agrupacion,
                'dias' => $fechaInicio->diffInDays($fechaFin),
            ]
        ]);
    }

    /**
     * Generar reporte de ventas
     */
    private function generarReporteVentas($fechaInicio, $fechaFin, $agrupacion)
    {
        $query = Pedido::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('estado_pedido', 'entregado');

        switch ($agrupacion) {
            case 'diaria':
                $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as pedidos, SUM(total) as monto_total')
                    ->groupBy('fecha')
                    ->orderBy('fecha');
                break;
            case 'semanal':
                $query->selectRaw('YEAR(created_at) as año, WEEK(created_at) as semana, COUNT(*) as pedidos, SUM(total) as monto_total')
                    ->groupBy('año', 'semana')
                    ->orderBy('año')
                    ->orderBy('semana');
                break;
            case 'mensual':
                $query->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as pedidos, SUM(total) as monto_total')
                    ->groupBy('año', 'mes')
                    ->orderBy('año')
                    ->orderBy('mes');
                break;
            case 'anual':
                $query->selectRaw('YEAR(created_at) as año, COUNT(*) as pedidos, SUM(total) as monto_total')
                    ->groupBy('año')
                    ->orderBy('año');
                break;
        }

        $ventas = $query->get();

        // Top vendedores
        $topVendedores = \App\Models\Comision::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('vendedor_id, COUNT(*) as ventas, SUM(monto_venta) as monto_total')
            ->groupBy('vendedor_id')
            ->orderBy('monto_total', 'desc')
            ->with('vendedor')
            ->limit(10)
            ->get();

        // Top productos
        $topProductos = \App\Models\PedidoItem::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('producto_id, SUM(cantidad) as cantidad_vendida, SUM(subtotal) as monto_total')
            ->groupBy('producto_id')
            ->orderBy('cantidad_vendida', 'desc')
            ->with('producto')
            ->limit(10)
            ->get();

        return [
            'ventas' => $ventas,
            'total_pedidos' => $ventas->sum('pedidos'),
            'total_monto' => $ventas->sum('monto_total'),
            'promedio_ticket' => $ventas->sum('pedidos') > 0 ? $ventas->sum('monto_total') / $ventas->sum('pedidos') : 0,
            'top_vendedores' => $topVendedores,
            'top_productos' => $topProductos,
        ];
    }

    /**
     * Generar reporte de usuarios
     */
    private function generarReporteUsuarios($fechaInicio, $fechaFin, $agrupacion)
    {
        $query = User::whereBetween('created_at', [$fechaInicio, $fechaFin]);

        switch ($agrupacion) {
            case 'diaria':
                $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as registros')
                    ->groupBy('fecha')
                    ->orderBy('fecha');
                break;
            case 'mensual':
                $query->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as registros')
                    ->groupBy('año', 'mes')
                    ->orderBy('año')
                    ->orderBy('mes');
                break;
        }

        $registros = $query->get();

        // Distribución por tipo
        $porTipo = User::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('tipo_usuario, COUNT(*) as cantidad')
            ->groupBy('tipo_usuario')
            ->get();

        // Usuarios más activos
        $masActivos = User::whereBetween('ultimo_acceso', [$fechaInicio, $fechaFin])
            ->orderBy('total_compras', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'total_compras', 'monto_total_compras', 'ultimo_acceso']);

        return [
            'registros' => $registros,
            'total_registros' => $registros->sum('registros'),
            'por_tipo' => $porTipo,
            'usuarios_activos' => User::whereBetween('ultimo_acceso', [$fechaInicio, $fechaFin])->count(),
            'usuarios_mas_activos' => $masActivos,
        ];
    }

    /**
     * Generar reporte de productos
     */
    private function generarReporteProductos($fechaInicio, $fechaFin, $agrupacion)
    {
        $query = Producto::whereBetween('created_at', [$fechaInicio, $fechaFin]);

        switch ($agrupacion) {
            case 'diaria':
                $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as productos')
                    ->groupBy('fecha')
                    ->orderBy('fecha');
                break;
            case 'mensual':
                $query->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as productos')
                    ->groupBy('año', 'mes')
                    ->orderBy('año')
                    ->orderBy('mes');
                break;
        }

        $productos = $query->get();

        // Productos más vendidos
        $masVendidos = Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('ventas', 'desc')
            ->limit(20)
            ->get(['id', 'nombre', 'ventas', 'vistas', 'stock', 'precio']);

        // Productos más vistos
        $masVistos = Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('vistas', 'desc')
            ->limit(20)
            ->get(['id', 'nombre', 'vistas', 'ventas', 'stock']);

        // Productos sin stock
        $sinStock = Producto::where('stock', 0)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->count();

        return [
            'productos' => $productos,
            'total_productos' => $productos->sum('productos'),
            'productos_mas_vendidos' => $masVendidos,
            'productos_mas_vistos' => $masVistos,
            'sin_stock' => $sinStock,
            'con_stock_bajo' => Producto::whereRaw('stock <= stock_minimo')->where('stock', '>', 0)->count(),
            'total_ventas' => Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('ventas'),
            'total_vistas' => Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('vistas'),
        ];
    }

    /**
     * Generar reporte de reclamos
     */
    private function generarReporteReclamos($fechaInicio, $fechaFin, $agrupacion)
    {
        $query = Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin]);

        switch ($agrupacion) {
            case 'diaria':
                $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as reclamos')
                    ->groupBy('fecha')
                    ->orderBy('fecha');
                break;
            case 'mensual':
                $query->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as reclamos')
                    ->groupBy('año', 'mes')
                    ->orderBy('año')
                    ->orderBy('mes');
                break;
        }

        $reclamos = $query->get();

        // Por tipo
        $porTipo = Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('tipo_reclamo, COUNT(*) as cantidad')
            ->groupBy('tipo_reclamo')
            ->get();

        // Por estado
        $porEstado = Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->get();

        // Por vendedor
        $porVendedor = Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('pedidos.vendedor_id, COUNT(*) as cantidad')
            ->join('pedidos', 'reclamos.pedido_id', '=', 'pedidos.id')
            ->groupBy('pedidos.vendedor_id')
            ->with('pedido.vendedor')
            ->orderBy('cantidad', 'desc')
            ->limit(10)
            ->get();

        return [
            'reclamos' => $reclamos,
            'total_reclamos' => $reclamos->sum('reclamos'),
            'por_tipo' => $porTipo,
            'por_estado' => $porEstado,
            'por_vendedor' => $porVendedor,
            'tiempo_respuesta_promedio' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereNotNull('tiempo_respuesta')
                ->avg('tiempo_respuesta'),
            'tasa_resolucion' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereIn('estado', ['resuelto', 'cerrado'])
                ->count() / max($reclamos->sum('reclamos'), 1) * 100,
        ];
    }

    /**
     * Generar reporte de finanzas
     */
    private function generarReporteFinanzas($fechaInicio, $fechaFin, $agrupacion)
    {
        $ventas = $this->generarReporteVentas($fechaInicio, $fechaFin, $agrupacion);

        // Comisiones
        $comisiones = \App\Models\Comision::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('estado, COUNT(*) as cantidad, SUM(monto_comision) as total_comisiones')
            ->groupBy('estado')
            ->get();

        // Pagos a vendedores
        $pagosVendedores = \App\Models\PagoVendedor::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('estado, COUNT(*) as cantidad, SUM(monto_total) as total_pagos')
            ->groupBy('estado')
            ->get();

        // Métodos de pago más usados
        $metodosPago = Pedido::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(total) as monto_total')
            ->groupBy('metodo_pago')
            ->get();

        return [
            'ventas' => $ventas,
            'comisiones' => $comisiones,
            'pagos_vendedores' => $pagosVendedores,
            'metodos_pago' => $metodosPago,
            'total_ventas' => $ventas['total_monto'],
            'total_comisiones' => $comisiones->sum('total_comisiones'),
            'total_pagos_vendedores' => $pagosVendedores->sum('total_pagos'),
            'ganancia_neta' => $ventas['total_monto'] - $comisiones->where('estado', 'pagada')->sum('total_comisiones'),
        ];
    }

    /**
     * Obtener actividad del sistema
     */
    public function getSystemActivity(Request $request)
    {
        $query = BitacoraSistema::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $actividad = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'actividad' => $actividad,
            'estadisticas' => [
                'total_registros' => $actividad->total(),
                'por_accion' => BitacoraSistema::selectRaw('accion, COUNT(*) as cantidad')
                    ->groupBy('accion')
                    ->orderBy('cantidad', 'desc')
                    ->get(),
                'por_usuario' => BitacoraSistema::selectRaw('user_id, COUNT(*) as cantidad')
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->orderBy('cantidad', 'desc')
                    ->with('user')
                    ->limit(10)
                    ->get(),
            ]
        ]);
    }

    /**
     * Enviar notificación masiva
     */
    public function sendMassNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => ['required', 'string', 'max:255'],
            'mensaje' => ['required', 'string'],
            'tipo' => ['required', 'in:sistema,promocion,importante'],
            'destinatarios' => ['required', 'array'],
            'destinatarios.*' => ['in:todos,compradores,vendedores,especificos'],
            'usuarios_especificos' => ['nullable', 'array', 'required_if:destinatarios,especificos'],
            'usuarios_especificos.*' => ['exists:users,id'],
            'programar' => ['boolean'],
            'fecha_envio' => ['nullable', 'date', 'required_if:programar,true'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Determinar destinatarios
        $destinatarios = collect();

        if (in_array('todos', $request->destinatarios)) {
            $destinatarios = $destinatarios->merge(User::all());
        } else {
            if (in_array('compradores', $request->destinatarios)) {
                $destinatarios = $destinatarios->merge(User::where('tipo_usuario', 'comprador')->get());
            }
            
            if (in_array('vendedores', $request->destinatarios)) {
                $destinatarios = $destinatarios->merge(User::where('tipo_usuario', 'vendedor')->get());
            }
            
            if (in_array('especificos', $request->destinatarios)) {
                $destinatarios = $destinatarios->merge(User::whereIn('id', $request->usuarios_especificos ?? [])->get());
            }
        }

        // Eliminar duplicados
        $destinatarios = $destinatarios->unique('id');

        DB::beginTransaction();
        try {
            $notificaciones = [];

            foreach ($destinatarios as $destinatario) {
                $notificaciones[] = [
                    'user_id' => $destinatario->id,
                    'titulo' => $request->titulo,
                    'mensaje' => $request->mensaje,
                    'tipo' => $request->tipo,
                    'leida' => false,
                    'programada_para' => $request->programar ? $request->fecha_envio : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            \App\Models\Notificacion::insert($notificaciones);

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'notificacion_masiva',
                null,
                null,
                "Notificación masiva enviada a {$destinatarios->count()} usuarios",
                null,
                [
                    'titulo' => $request->titulo,
                    'destinatarios' => $request->destinatarios,
                    'cantidad' => $destinatarios->count(),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Notificación enviada a {$destinatarios->count()} usuarios",
                'destinatarios' => $destinatarios->count(),
                'programada' => $request->programar,
                'fecha_envio' => $request->fecha_envio,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación masiva: ' . $e->getMessage()
            ], 500);
        }
    }
}