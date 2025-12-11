<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Producto;
use App\Models\Reclamo;
use App\Models\Comision;
use App\Models\CuponUso;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Dashboard de reportes para administración
     */
    public function index()
    {
        $hoy = now();
        $inicioMes = now()->startOfMonth();
        $inicioAnio = now()->startOfYear();

        $reporte = [
            'ventas' => [
                'hoy' => Pedido::whereDate('created_at', $hoy)->where('estado_pedido', 'entregado')->sum('total'),
                'mes' => Pedido::whereBetween('created_at', [$inicioMes, $hoy])->where('estado_pedido', 'entregado')->sum('total'),
                'anio' => Pedido::whereYear('created_at', $hoy->year)->where('estado_pedido', 'entregado')->sum('total'),
            ],
            'usuarios' => [
                'total' => User::count(),
                'nuevos_mes' => User::whereBetween('created_at', [$inicioMes, $hoy])->count(),
                'compradores_activos' => User::where('tipo_usuario', 'comprador')->where('total_compras', '>', 0)->count(),
            ],
            'vendedores' => [
                'total' => Vendedor::count(),
                'activos' => Vendedor::where('activo', true)->where('verificado', true)->count(),
                'ventas_mes' => Comision::whereBetween('created_at', [$inicioMes, $hoy])->sum('monto_vendedor'),
            ],
            'productos' => [
                'total' => Producto::count(),
                'activos' => Producto::where('activo', true)->where('aprobado', true)->count(),
                'mas_vendidos' => Producto::orderBy('ventas', 'desc')->limit(5)->get(['id', 'nombre', 'ventas']),
            ],
            'reclamos' => [
                'total' => Reclamo::count(),
                'abiertos' => Reclamo::whereIn('estado', ['abierto', 'en_revision'])->count(),
                'resueltos' => Reclamo::where('estado', 'resuelto')->count(),
                'tasa_resolucion' => Reclamo::count() > 0
                    ? (Reclamo::where('estado', 'resuelto')->count() / Reclamo::count()) * 100
                    : 0,
            ],
            'cupones' => [
                'usados_mes' => CuponUso::whereBetween('created_at', [$inicioMes, $hoy])->count(),
                'descuento_total_mes' => CuponUso::whereBetween('created_at', [$inicioMes, $hoy])->sum('descuento_aplicado'),
            ],
            'comisiones' => [
                'pendientes' => Comision::where('estado', 'pendiente')->sum('monto_comision'),
                'pagadas' => Comision::where('estado', 'pagada')->sum('monto_comision'),
            ]
        ];

        return response()->json([
            'success' => true,
            'reporte' => $reporte,
        ]);
    }

    /**
     * Reporte de ventas detallado
     */
    public function ventas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'agrupar_por' => ['nullable', 'in:dia,semana,mes'],
            'vendedor_id' => ['nullable', 'exists:vendedores,id'],
            'estado_pedido' => ['nullable', 'in:entregado,pendiente,confirmado,preparando,enviado,cancelado'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $agruparPor = $request->agrupar_por ?? 'dia';

        $query = Pedido::with(['user', 'vendedor'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin]);

        if ($request->vendedor_id) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        if ($request->estado_pedido) {
            $query->where('estado_pedido', $request->estado_pedido);
        } else {
            $query->where('estado_pedido', 'entregado'); // Por defecto, solo entregados
        }

        switch ($agruparPor) {
            case 'dia':
                $ventas = $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as pedidos, SUM(total) as total')
                    ->groupBy('fecha')
                    ->orderBy('fecha')
                    ->get();
                break;
            case 'semana':
                $ventas = $query->selectRaw('YEARWEEK(created_at, 1) as semana, COUNT(*) as pedidos, SUM(total) as total')
                    ->groupBy('semana')
                    ->orderBy('semana')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'semana' => $item->semana,
                            'pedidos' => $item->pedidos,
                            'total' => $item->total,
                            'fecha_inicio' => Carbon::parse($item->semana . ' Monday')->format('Y-m-d'),
                            'fecha_fin' => Carbon::parse($item->semana . ' Sunday')->format('Y-m-d'),
                        ];
                    });
                break;
            case 'mes':
                $ventas = $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes, COUNT(*) as pedidos, SUM(total) as total')
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
                break;
        }

        $topVendedores = Comision::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->with('vendedor.user')
            ->selectRaw('vendedor_id, SUM(monto_venta) as total_ventas, COUNT(*) as pedidos')
            ->groupBy('vendedor_id')
            ->orderBy('total_ventas', 'desc')
            ->limit(10)
            ->get();

        $topProductos = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('productos', 'pedido_items.producto_id', '=', 'productos.id')
            ->whereBetween('pedidos.created_at', [$fechaInicio, $fechaFin])
            ->where('pedidos.estado_pedido', 'entregado')
            ->selectRaw('productos.id, productos.nombre, SUM(pedido_items.cantidad) as cantidad, SUM(pedido_items.subtotal) as monto')
            ->groupBy('productos.id')
            ->orderBy('cantidad', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'parametros' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'agrupar_por' => $agruparPor,
                'vendedor_id' => $request->vendedor_id,
                'estado_pedido' => $request->estado_pedido ?? 'entregado',
            ],
            'ventas' => $ventas,
            'total_pedidos' => $ventas->sum('pedidos'),
            'total_ventas' => $ventas->sum('total'),
            'promedio_ticket' => $ventas->sum('pedidos') ? $ventas->sum('total') / $ventas->sum('pedidos') : 0,
            'top_vendedores' => $topVendedores,
            'top_productos' => $topProductos,
        ]);
    }

    /**
     * Reporte de usuarios y comportamiento
     */
    public function usuarios(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'tipo' => ['nullable', 'in:compradores,vendedores,ambos'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $tipo = $request->tipo ?? 'ambos';

        $query = User::whereBetween('created_at', [$fechaInicio, $fechaFin]);

        if ($tipo === 'compradores') {
            $query->where('tipo_usuario', 'comprador');
        } elseif ($tipo === 'vendedores') {
            $query->where('tipo_usuario', 'vendedor');
        }

        $usuarios = $query->get();

        $registroPorDia = $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $usuariosActivos = User::whereBetween('ultimo_acceso', [$fechaInicio, $fechaFin])->count();

        $compradoresConCompra = User::where('tipo_usuario', 'comprador')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('total_compras', '>', 0)
            ->count();

        $vendedoresConVenta = Vendedor::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('total_ventas', '>', 0)
            ->count();

        return response()->json([
            'success' => true,
            'parametros' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'tipo' => $tipo,
            ],
            'total_usuarios' => $usuarios->count(),
            'registro_por_dia' => $registroPorDia,
            'usuarios_activos' => $usuariosActivos,
            'compradores_con_compra' => $compradoresConCompra,
            'vendedores_con_venta' => $vendedoresConVenta,
            'por_estado' => User::whereIn('tipo_usuario', $tipo === 'ambos' ? ['comprador', 'vendedor'] : [$tipo])
                ->selectRaw('estado_id, COUNT(*) as total')
                ->with('estado')
                ->groupBy('estado_id')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Reporte de productos
     */
    public function productos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'ordenar_por' => ['nullable', 'in:ventas,vistas,stock,precio'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $ordenarPor = $request->ordenar_por ?? 'ventas';

        $productos = Producto::with('vendedor', 'categoria')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin]);

        switch ($ordenarPor) {
            case 'ventas':
                $productos = $productos->orderBy('ventas', 'desc');
                break;
            case 'vistas':
                $productos = $productos->orderBy('vistas', 'desc');
                break;
            case 'stock':
                $productos = $productos->orderBy('stock');
                break;
            case 'precio':
                $productos = $productos->orderBy('precio', 'desc');
                break;
        }

        $productos = $productos->paginate(25);

        $estadisticas = [
            'total_productos' => Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'activos' => Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->where('activo', true)->where('aprobado', true)->count(),
            'sin_stock' => Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->where('stock', 0)->count(),
            'stock_bajo' => Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereRaw('stock <= stock_minimo')->where('stock', '>', 0)->count(),
            'ventas_totales' => Producto::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('ventas'),
        ];

        return response()->json([
            'success' => true,
            'parametros' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'ordenar_por' => $ordenarPor,
            ],
            'productos' => $productos,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Reporte de reclamos
     */
    public function reclamos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'vendedor_id' => ['nullable', 'exists:vendedores,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);

        $query = Reclamo::with(['user', 'pedido.vendedor'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin]);

        if ($request->vendedor_id) {
            $vendedorId = $request->vendedor_id;
            $query->whereHas('pedido', function ($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        $reclamos = $query->orderBy('created_at', 'desc')->paginate(30);

        $estadisticas = [
            'total' => $reclamos->total(),
            'abiertos' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereIn('estado', ['abierto', 'en_revision'])->count(),
            'resueltos' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->where('estado', 'resuelto')->count(),
            'cerrados' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->where('estado', 'cerrado')->count(),
            'por_tipo' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->selectRaw('tipo_reclamo, COUNT(*) as total')
                ->groupBy('tipo_reclamo')
                ->get(),
            'por_prioridad' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->selectRaw('prioridad, COUNT(*) as total')
                ->groupBy('prioridad')
                ->get(),
            'tiempo_respuesta_promedio' => Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereNotNull('tiempo_respuesta')
                ->avg('tiempo_respuesta'),
        ];

        return response()->json([
            'success' => true,
            'parametros' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'vendedor_id' => $request->vendedor_id,
            ],
            'reclamos' => $reclamos,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Reporte financiero completo (ventas, comisiones, pagos)
     */
    public function financiero(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);

        $ventas = Pedido::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('estado_pedido', 'entregado')
            ->sum('total');

        $comisiones = Comision::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('estado', 'pagada')
            ->sum('monto_comision');

        $pagosVendedores = \App\Models\PagoVendedor::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('estado', 'completado')
            ->sum('monto_total');

        $reembolsos = Reclamo::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('reembolso_solicitado', true)
            ->sum('monto_reembolso');

        return response()->json([
            'success' => true,
            'parametros' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
            ],
            'finanzas' => [
                'ventas_netas' => $ventas,
                'comisiones_generadas' => $comisiones,
                'pagos_realizados_vendedores' => $pagosVendedores,
                'reembolsos_procesados' => $reembolsos,
                'ganancia_estimada' => $comisiones - $reembolsos,
            ]
        ]);
    }

    /**
     * Exportar reporte en formato JSON (para integración o descarga manual)
     */
    public function exportar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => ['required', 'in:ventas,usuarios,productos,reclamos,financiero'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Reutilizar los métodos internos para generar datos
        switch ($request->tipo) {
            case 'ventas':
                $datos = $this->ventas($request)->getData(true);
                break;
            case 'usuarios':
                $datos = $this->usuarios($request)->getData(true);
                break;
            case 'productos':
                $datos = $this->productos($request)->getData(true);
                break;
            case 'reclamos':
                $datos = $this->reclamos($request)->getData(true);
                break;
            case 'financiero':
                $datos = $this->financiero($request)->getData(true);
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Tipo de reporte no soportado'], 400);
        }

        return response()->json([
            'success' => true,
            'formato' => 'json',
            'reporte' => $datos,
            'fecha_exportacion' => now()->format('Y-m-d H:i:s'),
            'periodo' => $request->fecha_inicio . ' a ' . $request->fecha_fin,
        ]);
    }
}