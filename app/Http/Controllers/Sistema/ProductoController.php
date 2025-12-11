<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Mostrar listado de productos
     */
    public function index(Request $request)
    {
        try {
            $query = Producto::activos()
                ->aprobados()
                ->with(['vendedor', 'imagenes', 'categoria']);
            
            // Filtros
            if ($request->filled('categoria_id')) {
                $query->where('categoria_id', $request->categoria_id);
            }
            
            if ($request->filled('busqueda')) {
                $busqueda = $request->busqueda;
                $query->where(function($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('descripcion', 'like', "%{$busqueda}%")
                      ->orWhere('marca', 'like', "%{$busqueda}%")
                      ->orWhere('modelo', 'like', "%{$busqueda}%");
                });
            }
            
            if ($request->filled('precio_min')) {
                $query->where('precio', '>=', $request->precio_min);
            }
            
            if ($request->filled('precio_max')) {
                $query->where('precio', '<=', $request->precio_max);
            }
            
            if ($request->filled('oferta')) {
                $query->enOferta();
            }
            
            if ($request->filled('destacados')) {
                $query->destacados();
            }
            
            if ($request->filled('nuevos')) {
                $query->nuevos();
            }
            
            if ($request->filled('envio_gratis')) {
                $query->where('envio_gratis', true);
            }
            
            // Ordenamiento
            $orden = $request->get('orden', 'recientes');
            switch ($orden) {
                case 'precio_asc':
                    $query->orderBy('precio_actual', 'asc');
                    break;
                case 'precio_desc':
                    $query->orderBy('precio_actual', 'desc');
                    break;
                case 'nombre_asc':
                    $query->orderBy('nombre', 'asc');
                    break;
                case 'nombre_desc':
                    $query->orderBy('nombre', 'desc');
                    break;
                case 'mas_vendidos':
                    $query->orderBy('ventas', 'desc');
                    break;
                case 'mejor_valorados':
                    $query->orderBy('calificacion_promedio', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
            
            // Paginación
            $perPage = $request->get('per_page', 12);
            $productos = $query->paginate($perPage);
            
            // Obtener categorías para filtros
            $categorias = Categoria::activas()->get();
            
            // Obtener rango de precios
            $precioMin = Producto::activos()->aprobados()->min('precio_actual');
            $precioMax = Producto::activos()->aprobados()->max('precio_actual');
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'productos' => $productos,
                    'categorias' => $categorias,
                    'filtros' => [
                        'precio_min' => $precioMin,
                        'precio_max' => $precioMax
                    ],
                    'meta' => [
                        'titulo' => 'Productos - Monagas Vende',
                        'descripcion' => 'Encuentra los mejores productos electrónicos en Monagas Vende'
                    ]
                ]);
            }
            
            return view('productos.index', compact('productos', 'categorias', 'precioMin', 'precioMax'));
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar productos: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al cargar productos: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar un producto específico
     */
    public function show($id)
    {
        try {
            $producto = Producto::activos()
                ->aprobados()
                ->with([
                    'vendedor',
                    'imagenes',
                    'categoria',
                    'resenas' => function($query) {
                        $query->activas()->aprobadas()->with('user');
                    }
                ])
                ->findOrFail($id);
            
            // Incrementar vistas
            $producto->increment('vistas');
            
            // Productos relacionados (misma categoría)
            $relacionados = Producto::activos()
                ->aprobados()
                ->where('categoria_id', $producto->categoria_id)
                ->where('id', '!=', $producto->id)
                ->limit(4)
                ->get();
            
            // Obtener preguntas frecuentes
            $preguntas = $producto->preguntas()
                ->with(['respuestas' => function($q) {
                    $q->where('oficial', true)->orWhereNotNull('vendedor_id');
                }])
                ->whereHas('respuestas')
                ->orderBy('vistas', 'desc')
                ->limit(5)
                ->get();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'producto' => $producto,
                    'relacionados' => $relacionados,
                    'preguntas' => $preguntas,
                    'meta' => [
                        'titulo' => $producto->nombre . ' - Monagas Vende',
                        'descripcion' => substr($producto->descripcion, 0, 160)
                    ]
                ]);
            }
            
            return view('productos.show', compact('producto', 'relacionados', 'preguntas'));
            
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado: ' . $e->getMessage()
                ], 404);
            }
            
            return back()->with('error', 'Producto no encontrado');
        }
    }

    /**
     * Buscar productos
     */
    public function search(Request $request)
    {
        try {
            $query = Producto::activos()->aprobados()->with(['vendedor', 'imagenes']);
            
            if ($request->filled('q')) {
                $searchTerm = $request->q;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nombre', 'like', "%{$searchTerm}%")
                      ->orWhere('descripcion', 'like', "%{$searchTerm}%")
                      ->orWhere('marca', 'like', "%{$searchTerm}%")
                      ->orWhere('modelo', 'like', "%{$searchTerm}%")
                      ->orWhere('especificaciones', 'like', "%{$searchTerm}%")
                      ->orWhereHas('categoria', function($cat) use ($searchTerm) {
                          $cat->where('nombre', 'like', "%{$searchTerm}%");
                      });
                });
            }
            
            $productos = $query->orderBy('created_at', 'desc')->paginate(12);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'productos' => $productos,
                    'search_term' => $request->q ?? '',
                    'total' => $productos->total(),
                    'meta' => [
                        'titulo' => 'Buscar productos - Monagas Vende',
                        'descripcion' => 'Resultados de búsqueda en Monagas Vende'
                    ]
                ]);
            }
            
            return view('productos.search', compact('productos'));
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en la búsqueda: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error en la búsqueda: ' . $e->getMessage());
        }
    }

    /**
     * Obtener productos por categoría
     */
    public function byCategory($slug)
    {
        try {
            $categoria = Categoria::where('slug', $slug)->where('activo', true)->firstOrFail();
            
            $productos = Producto::activos()
                ->aprobados()
                ->where('categoria_id', $categoria->id)
                ->with(['vendedor', 'imagenes'])
                ->orderBy('created_at', 'desc')
                ->paginate(12);
            
            // Productos destacados de esta categoría
            $destacados = Producto::activos()
                ->aprobados()
                ->where('categoria_id', $categoria->id)
                ->destacados()
                ->limit(4)
                ->get();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'categoria' => $categoria,
                    'productos' => $productos,
                    'destacados' => $destacados,
                    'meta' => [
                        'titulo' => $categoria->nombre . ' - Monagas Vende',
                        'descripcion' => $categoria->descripcion
                    ]
                ]);
            }
            
            return view('productos.category', compact('categoria', 'productos', 'destacados'));
            
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría no encontrada: ' . $e->getMessage()
                ], 404);
            }
            
            return back()->with('error', 'Categoría no encontrada');
        }
    }

    /**
     * Comparar productos
     */
    public function compare(Request $request)
    {
        try {
            $ids = $request->get('ids', []);
            
            if (count($ids) < 2 || count($ids) > 4) {
                throw new \Exception('Debes seleccionar entre 2 y 4 productos para comparar');
            }
            
            $productos = Producto::activos()
                ->aprobados()
                ->whereIn('id', $ids)
                ->with(['vendedor', 'imagenes', 'categoria'])
                ->get();
            
            if ($productos->count() !== count($ids)) {
                throw new \Exception('Algunos productos no están disponibles');
            }
            
            // Obtener características para comparación
            $caracteristicas = [];
            foreach ($productos as $producto) {
                $especificaciones = $this->parseEspecificaciones($producto->especificaciones);
                foreach ($especificaciones as $key => $value) {
                    $caracteristicas[$key] = $key;
                }
            }
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'productos' => $productos,
                    'caracteristicas' => array_values($caracteristicas),
                    'meta' => [
                        'titulo' => 'Comparar productos - Monagas Vende',
                        'descripcion' => 'Comparativa de productos electrónicos'
                    ]
                ]);
            }
            
            return view('productos.compare', compact('productos', 'caracteristicas'));
            
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Parsear especificaciones del producto
     */
    private function parseEspecificaciones($especificaciones)
    {
        if (empty($especificaciones)) {
            return [];
        }
        
        $result = [];
        
        // Si es JSON
        if (json_decode($especificaciones)) {
            $data = json_decode($especificaciones, true);
            if (is_array($data)) {
                return $data;
            }
        }
        
        // Si es texto con formato clave: valor
        $lines = explode("\n", $especificaciones);
        foreach ($lines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $result[$key] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Obtener productos más vistos
     */
    public function mostViewed()
    {
        try {
            $productos = Producto::activos()
                ->aprobados()
                ->with(['vendedor', 'imagenes'])
                ->orderBy('vistas', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'productos' => $productos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos más vistos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos en oferta
     */
    public function onSale()
    {
        try {
            $productos = Producto::activos()
                ->aprobados()
                ->enOferta()
                ->with(['vendedor', 'imagenes'])
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            return response()->json([
                'success' => true,
                'productos' => $productos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos en oferta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar disponibilidad de stock
     */
    public function checkStock($id)
    {
        try {
            $producto = Producto::activos()->aprobados()->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'stock' => $producto->stock,
                'disponible' => $producto->stock > 0,
                'mensaje' => $producto->stock > 0 
                    ? 'Producto disponible' 
                    : 'Producto agotado'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado: ' . $e->getMessage()
            ], 404);
        }
    }
     // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeAprobados($query)
    {
        return $query->where('aprobado', true);
    }

    public function scopeDestacados($query)
    {
        return $query->where('destacado', true);
    }

    public function scopeEnOferta($query)
    {
        return $query->where('oferta', true)->whereNotNull('precio_descuento');
    }

    public function scopeNuevos($query)
    {
        return $query->where('nuevo', true);
    }

    // Calcular precio actual
    public function getPrecioActualAttribute()
    {
        return $this->precio_descuento ?? $this->precio;
    }

    // Calcular porcentaje de descuento
    public function getDescuentoPorcentajeAttribute()
    {
        if ($this->precio_descuento && $this->precio > 0) {
            $descuento = (($this->precio - $this->precio_descuento) / $this->precio) * 100;
            return round($descuento);
        }
        return 0;
    }

    // Relaciones
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class);
    }
}