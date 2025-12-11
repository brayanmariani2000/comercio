<?php
namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Comision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReporteVendedorController extends Controller
{
    /**
     * Dashboard de reportes del vendedor
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        // Fechas por defecto: último mes
        $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : now()->subMonth();
        $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : now();

        // Validar rango de fechas (máximo 6 meses)
        if ($fechaInicio->diffInMonths($fechaFin) > 6) {
            return response()->json([
                'success' => false,
                'message' => 'El rango de fechas no puede exceder los 6 meses'
            ], 422);
        }

        // Generar reporte
        $reporte = $vendedor->generarReporteVentas($fechaInicio, $fechaFin);

        // Productos más vendidos
        $productosMasVendidos = Producto::where('vendedor_id', $vendedor->id)
            ->withSum(['pedidoItems as cantidad_vendida' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereHas('pedido', function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                });
            }])
            ->withSum(['pedidoItems as ingresos' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereHas('pedido', function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                });
            }])
            ->orderBy('cantidad_vendida_sum', 'desc')
            ->limit(10)
            ->get(['id', 'nombre', 'precio', 'stock']);

        // Comisiones del período
        $comisiones = Comision::where('vendedor_id', $vendedor->id)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->get();

        return response()->json([
            'success' => true,
            'periodo' => [
                'inicio' => $fechaInicio->format('Y-m-d'),
                'fin' => $fechaFin->format('Y-m-d'),
                'dias' => $fechaInicio->diffInDays($fechaFin)
            ],
            'resumen' => $reporte,
            'productos_top' => $productosMasVendidos,
            'comisiones' => $comisiones,
            'comision_total' => $comisiones->sum('monto_comision'),
            'neto_vendedor' => $comisiones->sum('monto_vendedor'),
            'estado_membresia' => [
                'plan' => $vendedor->membresia,
                'vence' => $vendedor->fecha_vencimiento_membresia?->format('Y-m-d'),
                'activa' => $vendedor->tieneMembresiaActiva()
            ]
        ]);
    }

    /**
     * Exportar reporte a CSV
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : now()->subMonth();
        $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : now();

        $pedidos = Pedido::where('vendedor_id', $vendedor->id)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->with(['user', 'items.producto'])
            ->get();

        $csvData = [];
        foreach ($pedidos as $pedido) {
            foreach ($pedido->items as $item) {
                $csvData[] = [
                    'Número Pedido' => $pedido->numero_pedido,
                    'Fecha' => $pedido->created_at->format('Y-m-d H:i:s'),
                    'Cliente' => $pedido->nombre_cliente,
                    'Producto' => $item->producto->nombre,
                    'Cantidad' => $item->cantidad,
                    'Precio Unitario' => number_format($item->precio_unitario, 2, ',', '.'),
                    'Subtotal' => number_format($item->subtotal, 2, ',', '.'),
                    'Estado' => $pedido->estado_pedido,
                    'Método Pago' => $pedido->metodo_pago,
                    'Comisión' => $pedido->comision ? number_format($pedido->comision->monto_comision, 2, ',', '.') : '0,00',
                    'Neto' => $pedido->comision ? number_format($pedido->comision->monto_vendedor, 2, ',', '.') : '0,00'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $csvData,
            'total_filas' => count($csvData),
            'formato' => 'csv',
            'periodo' => $fechaInicio->format('d/m/Y') . ' - ' . $fechaFin->format('d/m/Y')
        ]);
    }

    /**
     * Reporte diario (últimos 30 días por defecto)
     */
    public function reporteDiario(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $dias = $request->dias ?? 30;
        $fechaInicio = now()->subDays($dias);

        $ventasDiarias = Pedido::selectRaw('DATE(created_at) as fecha, COUNT(*) as pedidos, SUM(total) as total_ventas')
            ->where('vendedor_id', $vendedor->id)
            ->where('estado_pedido', 'entregado')
            ->where('created_at', '>=', $fechaInicio)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'success' => true,
            'dias' => $dias,
            'ventas_diarias' => $ventasDiarias,
            'total_ventas' => $ventasDiarias->sum('total_ventas'),
            'promedio_diario' => $ventasDiarias->count() ? $ventasDiarias->sum('total_ventas') / $ventasDiarias->count() : 0
        ]);
    }

    /**
     * Reporte de productos (stock bajo, sin ventas, etc.)
     */
    public function reporteProductos(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $productos = Producto::where('vendedor_id', $vendedor->id)
            ->withCount(['pedidoItems as total_vendido' => function ($q) {
                $q->whereHas('pedido', fn($p) => $p->where('estado_pedido', 'entregado'));
            }])
            ->get();

        $stockBajo = $productos->where('stock', '<=', 'stock_minimo')->where('stock', '>', 0);
        $sinVentas = $productos->where('total_vendido_sum', 0);
        $agotados = $productos->where('stock', 0);
        $topVentas = $productos->sortByDesc('total_vendido_sum')->take(10);

        return response()->json([
            'success' => true,
            'total_productos' => $productos->count(),
            'stock_bajo' => $stockBajo,
            'sin_ventas' => $sinVentas,
            'agotados' => $agotados,
            'top_ventas' => $topVentas,
            'recomendaciones' => $this->generarRecomendaciones($productos)
        ]);
    }

    private function generarRecomendaciones($productos)
    {
        $recomendaciones = [];

        $sinVentas = $productos->where('total_vendido_sum', 0)->count();
        if ($sinVentas > 5) {
            $recomendaciones[] = "Tienes {$sinVentas} productos sin ventas. Considera aplicar descuentos o promociones.";
        }

        $stockBajo = $productos->where('stock', '<=', 'stock_minimo')->where('stock', '>', 0)->count();
        if ($stockBajo > 0) {
            $recomendaciones[] = "Tienes {$stockBajo} productos con stock bajo. Reabastece pronto para no perder ventas.";
        }

        $agotados = $productos->where('stock', 0)->count();
        if ($agotados > 0) {
            $recomendaciones[] = "Tienes {$agotados} productos agotados. Actualiza tu inventario.";
        }

        return $recomendaciones;
    }
}