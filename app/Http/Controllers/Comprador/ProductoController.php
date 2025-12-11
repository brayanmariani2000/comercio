<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Resena;
use App\Models\Pregunta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Listar productos con filtros y búsqueda
     */
    public function index(Request $request)
    {
        $query = Producto::activos()->with([
            'vendedor',
            'categoria',
            'imagenes',
            'resenas' => fn($q) => $q->aprobadas()
        ]);

        // Búsqueda por texto
        if ($request->filled('q')) {
            $query->buscar($request->q);
        }

        // Filtrar por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtrar por rango de precio
        if ($request->filled('precio_min') && $request->filled('precio_max')) {
            $query->whereBetween('precio', [$request->precio_min, $request->precio_max]);
        }

        // Filtrar por marca
        if ($request->filled('marca')) {
            $query->whereIn('marca', (array)$request->marca);
        }

        // Filtrar por condición
        if ($request->filled('condicion')) {
            $query->where('condicion', $request->condicion);
        }

        // Filtrar por envío gratis
        if ($request->filled('envio_gratis')) {
            $query->where('envio_gratis', true);
        }

        // Filtrar por oferta
        if ($request->filled('oferta')) {
            $query->where('oferta', true)->whereNotNull('precio_descuento');
        }

        // Filtrar por stock
        if ($request->filled('con_stock')) {
            $query->where('stock', '>', 0);
        }

        // Filtrar por vendedor
        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        // Ordenar
        $orden = $request->get('orden', 'mas_relevantes');
        $query->ordenarPor($orden);

        // Paginación
        $perPage = min($request->get('per_page', 20), 50);
        $productos = $query->paginate($perPage);

        // Información adicional de filtros
        $filtrosDisponibles = [];
        if ($request->filled('categoria_id')) {
            $categoria = Categoria::find($request->categoria_id);
            if ($categoria) {
                $filtrosDisponibles = $categoria->obtenerAtributosFiltrables();
            }
        } else {
            $filtrosDisponibles['marcas'] = Producto::activos()
                ->whereNotNull('marca')
                ->distinct('marca')
                ->pluck('marca');
            $filtrosDisponibles['precios'] = [
                'min' => Producto::activos()->min('precio') ?? 0,
                'max' => Producto::activos()->max('precio') ?? 10000000,
            ];
        }

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'filtros_disponibles' => $filtrosDisponibles,
            'meta' => [
                'total' => $productos->total(),
                'pagina_actual' => $productos->currentPage(),
                'ultima_pagina' => $productos->lastPage(),
                'por_pagina' => $productos->perPage(),
            ]
        ]);
    }

    /**
     * Mostrar detalle de un producto
     */
    public function show($id)
    {
        $producto = Producto::activos()
            ->with([
                'vendedor.user',
                'categoria',
                'imagenes',
                'resenas' => fn($q) => $q->aprobadas()->with('user')->latest()->paginate(5),
                'preguntas' => fn($q) => $q->activas()->with(['user', 'respuestas.vendedor'])->latest()->paginate(5),
                'pedidoItems.pedido'
            ])
            ->findOrFail($id);

        // Registrar vista
        $producto->aumentarVistas();

        // Verificar si el usuario ha comprado este producto
        $compraVerificada = false;
        if (auth()->check()) {
            $compraVerificada = auth()->user()->pedidos()
                ->whereHas('items', fn($q) => $q->where('producto_id', $id))
                ->where('estado_pedido', 'entregado')
                ->exists();
        }

        // Productos relacionados
        $relacionados = $producto->productosSimilares(8);
        $delMismoVendedor = $producto->productosDelMismoVendedor(6);

        // Estadísticas de reseñas
        $estadisticasResenas = [
            'promedio' => (float) $producto->calificacion_promedio,
            'total' => $producto->total_resenas,
            'distribucion' => collect(range(1, 5))->mapWithKeys(function($rating) use ($producto) {
                $count = $producto->resenas()->where('calificacion', $rating)->count();
                return [$rating => $count];
            }),
        ];

        return response()->json([
            'success' => true,
            'producto' => $producto,
            'especificaciones' => $producto->obtenerEspecificacionesFormateadas(),
            'galeria' => $producto->galeria,
            'compra_verificada' => $compraVerificada,
            'puede_resenar' => $compraVerificada && !$producto->resenas()->where('user_id', auth()->id())->exists(),
            'relacionados' => $relacionados,
            'del_mismo_vendedor' => $delMismoVendedor,
            'estadisticas_resenas' => $estadisticasResenas,
            'en_wishlist' => auth()->check() ? $producto->estaEnWishlist(auth()->id()) : false,
            'meta' => [
                'titulo' => $producto->nombre . ' - ' . $producto->vendedor->nombre_comercial,
                'descripcion' => Str::limit(strip_tags($producto->descripcion), 160),
                'imagen' => $producto->imagen_url,
            ]
        ]);
    }

    /**
     * Buscar productos (autocompletar)
     */
    public function buscar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $productos = Producto::activos()
            ->where('nombre', 'LIKE', "%{$request->q}%")
            ->limit(8)
            ->get(['id', 'nombre', 'precio', 'imagen_url', 'slug']);

        return response()->json([
            'success' => true,
            'sugerencias' => $productos
        ]);
    }

    /**
     * Productos destacados
     */
    public function destacados(Request $request)
    {
        $productos = Producto::activos()
            ->destacados()
            ->with(['vendedor', 'imagenes'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'titulo' => 'Productos Destacados'
        ]);
    }

    /**
     * Productos en oferta
     */
    public function ofertas(Request $request)
    {
        $productos = Producto::activos()
            ->enOferta()
            ->with(['vendedor', 'imagenes'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'titulo' => 'Ofertas del Día'
        ]);
    }

    /**
     * Productos nuevos
     */
    public function nuevos(Request $request)
    {
        $productos = Producto::activos()
            ->nuevos()
            ->with(['vendedor', 'imagenes'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'titulo' => 'Nuevos Productos'
        ]);
    }

    /**
     * Agregar a wishlist (desde la vista de producto)
     */
    public function addToWishlist(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para usar esta función'
            ], 401);
        }

        $user = auth()->user();
        $producto = Producto::activos()->findOrFail($id);

        try {
            $user->agregarProductoAWishlist($producto->id);
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado a tu lista de deseos'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verificar disponibilidad de stock
     */
    public function verificarStock($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'stock' => $producto->stock,
            'disponible' => $producto->stock > 0,
            'stock_minimo' => $producto->stock_minimo,
            'stock_bajo' => $producto->stock <= $producto->stock_minimo && $producto->stock > 0,
        ]);
    }

    /**
     * Comparar productos (mínimo 2, máximo 4)
     */
    public function comparar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productos' => 'required|array|min:2|max:4',
            'productos.*' => 'exists:productos,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $productos = Producto::activos()
            ->whereIn('id', $request->productos)
            ->with(['vendedor', 'categoria', 'imagenes'])
            ->get();

        if ($productos->count() !== count($request->productos)) {
            return response()->json(['success' => false, 'message' => 'Algunos productos no están disponibles'], 400);
        }

        // Determinar atributos comunes para comparación
        $atributos = ['Precio', 'Marca', 'Modelo', 'Condición', 'Garantía', 'Envío Gratis'];
        $especificaciones = $productos->pluck('especificaciones')->flatten()->keys()->unique();

        $tabla = $productos->map(function($p) use ($atributos, $especificaciones) {
            $fila = [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'imagen' => $p->imagen_url,
                'vendedor' => $p->vendedor->nombre_comercial,
            ];
            foreach ($atributos as $attr) {
                $fila[$attr] = match ($attr) {
                    'Precio' => number_format($p->precio_actual, 2, ',', '.'),
                    'Marca' => $p->marca ?? '—',
                    'Modelo' => $p->modelo ?? '—',
                    'Condición' => ucfirst($p->condicion),
                    'Garantía' => $p->garantia ?? 'No especificada',
                    'Envío Gratis' => $p->envio_gratis ? 'Sí' : 'No',
                    default => '—'
                };
            }
            foreach ($especificaciones as $esp) {
                $fila[$esp] = $p->especificaciones[$esp] ?? '—';
            }
            return $fila;
        });

        return response()->json([
            'success' => true,
            'productos' => $tabla,
            'atributos_estaticos' => $atributos,
            'atributos_dinamicos' => $especificaciones->values(),
            'total_productos' => $productos->count()
        ]);
    }
}