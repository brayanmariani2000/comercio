<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Reclamo;
use App\Models\Resena;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return redirect()->route('home')->with('error', 'No tienes perfil de vendedor asociado.');
        }

        // Fechas
        $hoy = now();
        $inicioMes = now()->startOfMonth();

        // Estadísticas
        $estadisticas = [
            'ventas_total' => $vendedor->pedidosCompletados()
                ->whereBetween('created_at', [$inicioMes, $hoy])
                ->sum('total'),
            'pedidos_mes' => $vendedor->pedidosCompletados()
                ->whereBetween('created_at', [$inicioMes, $hoy])
                ->count(),
            'productos_activos' => $vendedor->productosActivos()->count(),
            'total_productos' => $vendedor->productos()->count(),
            'calificacion' => $vendedor->calificacion_promedio,
        ];

        // Pedidos recientes
        $pedidosRecientes = $vendedor->pedidos()
            ->with(['user', 'items.producto'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Productos con stock bajo
        $productosStockBajo = $vendedor->productos()
            ->where('stock', '<=', \DB::raw('stock_minimo'))
            ->where('stock', '>', 0)
            ->limit(5)
            ->get();

        return view('vendedor.dashboard', compact('vendedor', 'estadisticas', 'pedidosRecientes', 'productosStockBajo'));
    }
}
