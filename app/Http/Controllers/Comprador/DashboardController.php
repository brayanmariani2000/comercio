<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Wishlist;
use App\Models\Notificacion;
use App\Models\Vendedor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard principal del comprador
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Fechas para estadísticas
        $hoy = now();
        $inicioMes = now()->startOfMonth();
        $inicioSemana = now()->startOfWeek();

        // Estadísticas del comprador
        $estadisticas = [
            'compras' => [
                'total' => $user->total_compras,
                'total_monto' => (float) $user->monto_total_compras,
                'compras_mes' => Pedido::where('user_id', $user->id)
                    ->whereBetween('created_at', [$inicioMes, $hoy])
                    ->where('estado_pedido', 'entregado')
                    ->count(),
                'compras_hoy' => Pedido::where('user_id', $user->id)
                    ->whereDate('created_at', $hoy)
                    ->where('estado_pedido', 'entregado')
                    ->count(),
                'pedidos_pendientes' => Pedido::where('user_id', $user->id)
                    ->whereIn('estado_pedido', ['pendiente', 'confirmado', 'preparando'])
                    ->count(),
                'ultima_compra' => $user->pedidosCompletados()->latest()->first()?->created_at?->format('d/m/Y'),
            ],
            'interacciones' => [
                'notificaciones_no_leidas' => $user->notificacionesNoLeidas()->count(),
                'wishlist_items' => $user->wishlists()->with('items')->get()->sum(function($w) {
                    return $w->items->count();
                }),
                'reseñas_creadas' => $user->resenas()->count(),
                'preguntas_realizadas' => $user->preguntas()->count(),
            ],
            'preferencias' => [
                'categorias_favoritas' => $user->pedidosCompletados()
                    ->join('pedido_items', 'pedidos.id', '=', 'pedido_items.pedido_id')
                    ->join('productos', 'pedido_items.producto_id', '=', 'productos.id')
                    ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                    ->selectRaw('categorias.id, categorias.nombre, COUNT(*) as total')
                    ->groupBy('categorias.id', 'categorias.nombre')
                    ->orderBy('total', 'desc')
                    ->limit(3)
                    ->get(),
                'vendedores_favoritos' => Vendedor::whereIn('id', function($query) use ($user) {
                    $query->select('vendedor_id')
                        ->from('pedidos')
                        ->where('user_id', $user->id)
                        ->where('estado_pedido', 'entregado');
                })->withCount(['pedidos as compras'])
                    ->orderBy('compras', 'desc')
                    ->limit(3)
                    ->get(['id', 'nombre_comercial', 'compras_count']),
            ]
        ];

        // Recomendaciones personalizadas
        $recomendaciones = $this->obtenerRecomendaciones($user);

        // Notificaciones recientes
        $notificacionesRecientes = $user->notificaciones()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Últimos pedidos
        $pedidosRecientes = $user->pedidos()
            ->with(['vendedor', 'items.producto.imagenes'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Productos en wishlist
        $wishlistProductos = Wishlist::where('user_id', $user->id)
            ->with(['items.producto.vendedor', 'items.producto.imagenes'])
            ->first()?->items->take(6) ?? collect();

        // Productos vistos recientemente
        $productosVistos = $user->seguimientosProductos()
            ->where('accion', 'visto')
            ->with('producto.vendedor')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->pluck('producto');

        return view('comprador.dashboard', compact(
            'estadisticas',
            'recomendaciones',
            'notificacionesRecientes',
            'pedidosRecientes',
            'wishlistProductos',
            'productosVistos'
        ));
    }

    /**
     * Obtener recomendaciones personalizadas
     */
    private function obtenerRecomendaciones($user)
    {
        $recomendaciones = [];

        // Productos similares a los comprados
        $ultimosProductos = $user->pedidosCompletados()
            ->join('pedido_items', 'pedidos.id', '=', 'pedido_items.pedido_id')
            ->join('productos', 'pedido_items.producto_id', '=', 'productos.id')
            ->orderBy('pedidos.created_at', 'desc')
            ->limit(3)
            ->pluck('productos.categoria_id')
            ->unique();

        if ($ultimosProductos->isNotEmpty()) {
            $productosSimilares = Producto::activos()
                ->whereIn('categoria_id', $ultimosProductos)
                ->whereNotIn('id', function($q) use ($user) {
                    $q->select('producto_id')
                        ->from('pedido_items')
                        ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
                        ->where('pedidos.user_id', $user->id);
                })
                ->inRandomOrder()
                ->limit(6)
                ->get(['id', 'nombre', 'precio'])
                ->append('imagen_url');
            $recomendaciones['basado_en_compras'] = $productosSimilares;
        }

        // Ofertas en categorías compradas
        $ofertas = Producto::activos()
            ->enOferta()
            ->where('precio_descuento', '>', 0)
            ->whereIn('categoria_id', $ultimosProductos)
            ->inRandomOrder()
            ->limit(4)
            ->get(['id', 'nombre', 'precio', 'precio_descuento'])
            ->append(['imagen_url', 'descuento_porcentaje']);
        $recomendaciones['ofertas_para_ti'] = $ofertas;

        // Productos en wishlist con descuento
        $wishlistIds = Wishlist::where('user_id', $user->id)
            ->with('items')
            ->first()?->items->pluck('producto_id') ?? collect();

        if ($wishlistIds->isNotEmpty()) {
            $enOferta = Producto::whereIn('id', $wishlistIds)
                ->where('oferta', true)
                ->whereNotNull('precio_descuento')
                ->get(['id', 'nombre', 'precio', 'precio_descuento'])
                ->append('imagen_url');
            $recomendaciones['wishlist_en_oferta'] = $enOferta;
        }

        return $recomendaciones;
    }

    /**
     * Resumen rápido del comprador (para widgets móviles)
     */
    public function resumen(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'resumen' => [
                'compras_totales' => $user->total_compras,
                'monto_gastado' => (float) $user->monto_total_compras,
                'pedidos_pendientes' => Pedido::where('user_id', $user->id)
                    ->whereIn('estado_pedido', ['pendiente', 'confirmado', 'preparando', 'enviado'])
                    ->count(),
                'notificaciones_no_leidas' => $user->notificacionesNoLeidas()->count(),
                'productos_en_wishlist' => Wishlist::where('user_id', $user->id)
                    ->withCount('items')
                    ->get()
                    ->sum('items_count'),
                'calificacion_promedio' => (float) $user->rating_promedio,
                'nivel_usuario' => $this->determinarNivelUsuario($user->total_compras),
            ]
        ]);
    }

    /**
     * Determinar nivel del comprador
     */
    private function determinarNivelUsuario($compras)
    {
        if ($compras >= 50) return 'Platino';
        if ($compras >= 20) return 'Oro';
        if ($compras >= 10) return 'Plata';
        if ($compras >= 3) return 'Bronce';
        return 'Nuevo';
    }

    /**
     * Historial de compras resumido
     */
    public function historialCompras(Request $request)
    {
        $user = $request->user();
        $pedidos = $user->pedidos()
            ->with(['vendedor', 'items.producto'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $resumen = [
            'total_pedidos' => $user->total_compras,
            'total_gastado' => (float) $user->monto_total_compras,
            'promedio_ticket' => $user->total_compras > 0 ? (float)($user->monto_total_compras / $user->total_compras) : 0,
            'meses_activos' => Pedido::where('user_id', $user->id)
                ->selectRaw('COUNT(DISTINCT YEAR(created_at), MONTH(created_at)) as meses')
                ->first()->meses ?? 0,
        ];

        return response()->json([
            'success' => true,
            'pedidos' => $pedidos,
            'resumen' => $resumen
        ]);
    }

    /**
     * Preferencias del comprador
     */
    public function preferencias(Request $request)
    {
        if ($request->isMethod('get')) {
            $preferencias = $request->user()->preferencias ?? [];
            $default = [
                'email_boletines' => true,
                'email_promociones' => true,
                'email_pedidos' => true,
                'email_reclamos' => true,
                'notificaciones_push' => true,
                'notificaciones_email' => true,
                'moneda_preferida' => 'Bs.',
            ];
            return response()->json([
                'success' => true,
                'preferencias' => array_merge($default, $preferencias)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'email_boletines' => 'boolean',
            'email_promociones' => 'boolean',
            'email_pedidos' => 'boolean',
            'email_reclamos' => 'boolean',
            'notificaciones_push' => 'boolean',
            'notificaciones_email' => 'boolean',
            'moneda_preferida' => 'in:Bs.,USD',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $user->preferencias = array_merge($user->preferencias ?? [], $request->only([
            'email_boletines',
            'email_promociones',
            'email_pedidos',
            'email_reclamos',
            'notificaciones_push',
            'notificaciones_email',
            'moneda_preferida'
        ]));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferencias actualizadas',
            'preferencias' => $user->preferencias
        ]);
    }
}