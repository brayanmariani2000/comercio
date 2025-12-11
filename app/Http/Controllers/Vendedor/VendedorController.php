<?php
namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendedor;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Reclamo;
use App\Models\Resena;
use Carbon\Carbon;

class VendedorController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        // Fechas
        $hoy = now();
        $inicioMes = now()->startOfMonth();
        $inicioSemana = now()->startOfWeek();

        // Estadísticas generales
        $estadisticas = [
            'ventas' => [
                'total_mes' => $vendedor->pedidosCompletados()
                    ->whereBetween('created_at', [$inicioMes, $hoy])
                    ->sum('total'),
                'pedidos_mes' => $vendedor->pedidosCompletados()
                    ->whereBetween('created_at', [$inicioMes, $hoy])
                    ->count(),
                'ventas_hoy' => $vendedor->pedidosCompletados()
                    ->whereDate('created_at', $hoy)
                    ->count(),
                'promedio_ticket' => $vendedor->pedidosCompletados()
                    ->avg('total') ?? 0,
            ],
            'productos' => [
                'total' => $vendedor->productos()->count(),
                'activos' => $vendedor->productosActivos()->count(),
                'sin_stock' => $vendedor->productos()->where('stock', 0)->count(),
                'stock_bajo' => $vendedor->productos()->whereRaw('stock <= stock_minimo')->where('stock', '>', 0)->count(),
                'mas_vendidos' => Producto::where('vendedor_id', $vendedor->id)
                    ->orderBy('ventas', 'desc')
                    ->limit(5)
                    ->get(['id', 'nombre', 'ventas', 'stock']),
            ],
            'pedidos' => [
                'pendientes' => $vendedor->pedidosPendientes()->count(),
                'hoy' => $vendedor->pedidos()->whereDate('created_at', $hoy)->count(),
                'reclamos_abiertos' => Reclamo::whereHas('pedido', fn($q) => $q->where('vendedor_id', $vendedor->id))
                    ->whereIn('estado', ['abierto', 'en_revision'])
                    ->count(),
            ],
            'interacciones' => [
                'reseñas_nuevas' => Resena::whereHas('producto', fn($q) => $q->where('vendedor_id', $vendedor->id))
                    ->where('created_at', '>=', now()->subWeek())
                    ->where('aprobada', false)
                    ->count(),
                'preguntas_nuevas' => \App\Models\Pregunta::whereHas('producto', fn($q) => $q->where('vendedor_id', $vendedor->id))
                    ->where('created_at', '>=', now()->subWeek())
                    ->whereDoesntHave('respuestas')
                    ->count(),
                'mensaje_no_leido' => \App\Models\Mensaje::whereHas('conversacion', fn($q) => $q->where('vendedor_id', $vendedor->id))
                    ->where('leido', false)
                    ->where('user_id', '!=', null)
                    ->count(),
            ]
        ];

        // Pedidos recientes
        $pedidosRecientes = $vendedor->pedidos()
            ->with(['user', 'items.producto'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Productos con stock bajo
        $productosStockBajo = $vendedor->productos()
            ->where('stock', '<=', \DB::raw('stock_minimo'))
            ->where('stock', '>', 0)
            ->limit(5)
            ->get(['id', 'nombre', 'stock', 'stock_minimo']);

        // Reclamos recientes
        $reclamosRecientes = Reclamo::whereHas('pedido', fn($q) => $q->where('vendedor_id', $vendedor->id))
            ->with(['user', 'pedido'])
            ->whereIn('estado', ['abierto', 'en_revision'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Estado de membresía
        $membresia = [
            'plan' => $vendedor->membresia,
            'vence' => $vendedor->fecha_vencimiento_membresia?->format('d/m/Y'),
            'dias_restantes' => $vendedor->fecha_vencimiento_membresia?->diffInDays(now(), false) ?? 0,
            'activa' => $vendedor->tieneMembresiaActiva(),
            'limite_productos' => $vendedor->obtenerLimiteProductos(),
            'productos_publicados' => $vendedor->productos()->count(),
        ];

        return response()->json([
            'success' => true,
            'estadisticas' => $estadisticas,
            'pedidos_recientes' => $pedidosRecientes,
            'productos_stock_bajo' => $productosStockBajo,
            'reclamos_recientes' => $reclamosRecientes,
            'membresia' => $membresia,
            'calificacion' => $vendedor->calificacion_promedio,
            'total_ventas' => $vendedor->total_ventas,
        ]);
    }

    public function overview(Request $request)
    {
        // Resumen semanal para gráficos
        $inicioSemana = now()->subDays(7);
        $ventasPorDia = Pedido::where('vendedor_id', $request->user()->vendedor->id)
            ->where('estado_pedido', 'entregado')
            ->whereBetween('created_at', [$inicioSemana, now()])
            ->selectRaw('DATE(created_at) as fecha, SUM(total) as total, COUNT(*) as pedidos')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = now()->subDays($i)->format('Y-m-d');
            $dias[$dia] = [
                'fecha' => $dia,
                'total' => 0,
                'pedidos' => 0
            ];
        }

        foreach ($ventasPorDia as $venta) {
            $dias[$venta->fecha] = [
                'fecha' => $venta->fecha,
                'total' => (float) $venta->total,
                'pedidos' => (int) $venta->pedidos
            ];
        }

        return response()->json([
            'success' => true,
            'ventas_semanales' => array_values($dias),
        ]);
    }
}