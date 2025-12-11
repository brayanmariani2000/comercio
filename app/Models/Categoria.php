<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'icono',
        'imagen',
        'orden',
        'activo',
        'categoria_padre_id',
        'meta_titulo',
        'meta_descripcion',
        'meta_keywords',
        'mostrar_en_inicio',
        'destacada',
        'color',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'mostrar_en_inicio' => 'boolean',
        'destacada' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nombre);
            }
        });
    }

    // RELACIONES
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function productosActivos()
    {
        return $this->hasMany(Producto::class)
            ->where('activo', true)
            ->where('aprobado', true);
    }

    public function productosDestacados()
    {
        return $this->productosActivos()->where('destacado', true);
    }

    public function productosEnOferta()
    {
        return $this->productosActivos()->where('oferta', true);
    }

    public function subcategorias()
    {
        return $this->hasMany(Categoria::class, 'categoria_padre_id');
    }

    public function padre()
    {
        return $this->belongsTo(Categoria::class, 'categoria_padre_id');
    }

    // MÉTODOS
    public function getImagenUrlAttribute()
    {
        return $this->imagen ? Storage::url($this->imagen) : asset('images/default-category.jpg');
    }

    public function getRutaCompletaAttribute()
    {
        $ruta = [];
        $categoria = $this;
        
        while ($categoria) {
            $ruta[] = $categoria;
            $categoria = $categoria->padre;
        }
        
        return array_reverse($ruta);
    }

    public function getNombreCompletoAttribute()
    {
        $ruta = $this->ruta_completa;
        return implode(' > ', array_column($ruta, 'nombre'));
    }

    public function esPadre()
    {
        return $this->subcategorias()->count() > 0;
    }

    public function esHijo()
    {
        return !is_null($this->categoria_padre_id);
    }

    public function obtenerProductosPorFiltros($filtros = [], $paginate = 20)
    {
        $query = $this->productosActivos();
        
        // Filtrar por precio
        if (isset($filtros['precio_min']) && isset($filtros['precio_max'])) {
            $query->whereBetween('precio', [$filtros['precio_min'], $filtros['precio_max']]);
        }
        
        // Filtrar por marca
        if (isset($filtros['marca']) && !empty($filtros['marca'])) {
            $query->whereIn('marca', (array)$filtros['marca']);
        }
        
        // Filtrar por condición
        if (isset($filtros['condicion']) && !empty($filtros['condicion'])) {
            $query->where('condicion', $filtros['condicion']);
        }
        
        // Filtrar por envío gratis
        if (isset($filtros['envio_gratis'])) {
            $query->where('envio_gratis', true);
        }
        
        // Ordenar
        $orden = $filtros['orden'] ?? 'mas_relevantes';
        switch ($orden) {
            case 'precio_asc':
                $query->orderBy('precio');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            case 'mas_vendidos':
                $query->orderBy('ventas', 'desc');
                break;
            case 'mejor_calificados':
                $query->orderBy('calificacion_promedio', 'desc');
                break;
            case 'mas_recientes':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('ventas', 'desc');
        }
        
        return $query->paginate($paginate);
    }

    public function obtenerEstadisticas()
    {
        $totalProductos = $this->productosActivos()->count();
        $productosConStock = $this->productosActivos()->where('stock', '>', 0)->count();
        $productosSinStock = $totalProductos - $productosConStock;
        $productosEnOferta = $this->productosEnOferta()->count();
        
        $ventasTotales = $this->productos()->sum('ventas');
        $precioPromedio = $this->productosActivos()->avg('precio');
        
        $marcas = $this->productosActivos()
            ->select('marca')
            ->whereNotNull('marca')
            ->groupBy('marca')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->pluck('marca');
        
        return [
            'total_productos' => $totalProductos,
            'productos_con_stock' => $productosConStock,
            'productos_sin_stock' => $productosSinStock,
            'productos_en_oferta' => $productosEnOferta,
            'ventas_totales' => $ventasTotales,
            'precio_promedio' => round($precioPromedio, 2),
            'marcas_populares' => $marcas,
            'rating_promedio' => $this->productosActivos()->avg('calificacion_promedio'),
        ];
    }

    public function obtenerAtributosFiltrables()
    {
        // Obtener atributos únicos de los productos de esta categoría
        $especificaciones = $this->productosActivos()
            ->whereNotNull('especificaciones')
            ->pluck('especificaciones')
            ->flatMap(function ($esp) {
                return is_array($esp) ? array_keys($esp) : [];
            })
            ->unique()
            ->values();
        
        $marcas = $this->productosActivos()
            ->whereNotNull('marca')
            ->distinct('marca')
            ->pluck('marca');
        
        $colores = $this->productosActivos()
            ->whereNotNull('color')
            ->distinct('color')
            ->pluck('color');
        
        $condiciones = $this->productosActivos()
            ->whereNotNull('condicion')
            ->distinct('condicion')
            ->pluck('condicion');
        
        return [
            'marcas' => $marcas,
            'colores' => $colores,
            'condiciones' => $condiciones,
            'especificaciones' => $especificaciones,
            'precio_min' => $this->productosActivos()->min('precio') ?? 0,
            'precio_max' => $this->productosActivos()->max('precio') ?? 10000,
        ];
    }

    public function obtenerProductosRecomendados($limit = 12)
    {
        return $this->productosActivos()
            ->with('vendedor')
            ->where('destacado', true)
            ->orWhere('ventas', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    // SCOPE
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePrincipales($query)
    {
        return $query->whereNull('categoria_padre_id');
    }

    public function scopeDestacadas($query)
    {
        return $query->where('destacada', true);
    }

    public function scopeMostrarEnInicio($query)
    {
        return $query->where('mostrar_en_inicio', true);
    }

    public function scopeConProductos($query)
    {
        return $query->whereHas('productos', function($q) {
            $q->where('activo', true)->where('aprobado', true);
        });
    }

    public function scopePorSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'LIKE', "%{$termino}%")
            ->orWhere('descripcion', 'LIKE', "%{$termino}%");
    }
}