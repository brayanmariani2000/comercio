<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendedor_id',
        'categoria_id',
        'codigo',
        'sku',
        'nombre',
        'slug',
        'descripcion',
        'especificaciones',
        'precio',
        'precio_descuento',
        'stock',
        'stock_minimo',
        'marca',
        'modelo',
        'garantia',
        'nuevo',
        'destacado',
        'oferta',
        'envio_gratis',
        'costo_envio',
        'dias_entrega',
        'ventas',
        'vistas',
        'calificacion_promedio',
        'total_resenas',
        'activo',
        'aprobado',
        'tipo_envio',
        'peso',
        'dimensiones',
        'color',
        'condicion',
        'tags',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_descuento' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'nuevo' => 'boolean',
        'destacado' => 'boolean',
        'oferta' => 'boolean',
        'envio_gratis' => 'boolean',
        'costo_envio' => 'decimal:2',
        'ventas' => 'integer',
        'vistas' => 'integer',
        'calificacion_promedio' => 'decimal:2',
        'total_resenas' => 'integer',
        'activo' => 'boolean',
        'aprobado' => 'boolean',
        'especificaciones' => 'array',
        'tags' => 'array',
        'dimensiones' => 'array',
        'peso' => 'decimal:3',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($producto) {
            if (empty($producto->slug)) {
                $producto->slug = Str::slug($producto->nombre);
            }
            
            if (empty($producto->codigo)) {
                $producto->codigo = 'PROD-' . Str::upper(Str::random(8));
            }
            
            if (empty($producto->sku)) {
                $producto->sku = 'SKU-' . time() . '-' . Str::random(4);
            }
        });
        
        static::updating(function ($producto) {
            // Guardar historial de precio si cambia
            if ($producto->isDirty('precio') || $producto->isDirty('precio_descuento')) {
                HistorialPrecio::crearDesdeProducto($producto);
            }
        });
    }

    // RELACIONES
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
        return $this->hasMany(ProductoImagen::class)->orderBy('orden');
    }

    public function imagenPrincipal()
    {
        return $this->hasOne(ProductoImagen::class)->where('principal', true);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class)->where('aprobada', true)->where('activa', true);
    }

    public function resenasVerificadas()
    {
        return $this->resenas()->where('verificada_compra', true);
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class);
    }

    public function preguntasRespondidas()
    {
        return $this->hasMany(Pregunta::class)->whereHas('respuestas');
    }

    public function carritoItems()
    {
        return $this->hasMany(CarritoItem::class);
    }

    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function historialPrecios()
    {
        return $this->hasMany(HistorialPrecio::class);
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoProducto::class);
    }

    public function conversaciones()
    {
        return $this->hasMany(Conversacion::class);
    }

    // MÉTODOS
    public function getPrecioActualAttribute()
    {
        return $this->precio_descuento ?? $this->precio;
    }

    public function getDescuentoPorcentajeAttribute()
    {
        if (!$this->precio_descuento || $this->precio <= 0) {
            return 0;
        }
        
        $descuento = (($this->precio - $this->precio_descuento) / $this->precio) * 100;
        return round($descuento, 0);
    }

    public function getImagenUrlAttribute()
    {
        $imagen = $this->imagenPrincipal ?? $this->imagenes()->first();
        return $imagen ? Storage::url($imagen->imagen) : asset('images/default-product.jpg');
    }

    public function getGaleriaAttribute()
    {
        return $this->imagenes->map(function($imagen) {
            return [
                'id' => $imagen->id,
                'url' => Storage::url($imagen->imagen),
                'principal' => $imagen->principal,
                'orden' => $imagen->orden
            ];
        });
    }

    public function aumentarVistas()
    {
        $this->increment('vistas');
        
        // Registrar seguimiento si hay usuario autenticado
        if (auth()->check()) {
            SeguimientoProducto::registrar(auth()->id(), $this->id, 'visto');
        }
    }

    public function registrarVenta($cantidad = 1)
    {
        $this->decrement('stock', $cantidad);
        $this->increment('ventas', $cantidad);
        
        // Notificar al vendedor si el stock está bajo
        if ($this->stock <= $this->stock_minimo) {
            $this->vendedor->user->generarNotificacion(
                'Stock bajo',
                "El producto {$this->nombre} tiene stock bajo ({$this->stock} unidades)",
                'producto',
                ['producto_id' => $this->id]
            );
        }
    }

    public function actualizarRating()
    {
        $resenas = $this->resenas;
        
        if ($resenas->count() > 0) {
            $this->calificacion_promedio = $resenas->avg('calificacion');
            $this->total_resenas = $resenas->count();
        } else {
            $this->calificacion_promedio = 0;
            $this->total_resenas = 0;
        }
        
        $this->save();
    }

    public function agregarImagen($imagenPath, $principal = false)
    {
        if ($principal) {
            // Quitar principal de otras imágenes
            $this->imagenes()->update(['principal' => false]);
        }
        
        return $this->imagenes()->create([
            'imagen' => $imagenPath,
            'principal' => $principal,
            'orden' => $this->imagenes()->count()
        ]);
    }

    public function obtenerEspecificacionesFormateadas()
    {
        if (empty($this->especificaciones)) {
            return [];
        }
        
        return collect($this->especificaciones)->map(function($value, $key) {
            return [
                'nombre' => $key,
                'valor' => $value
            ];
        });
    }

    public function productosSimilares($limit = 8)
    {
        return self::where('categoria_id', $this->categoria_id)
            ->where('id', '!=', $this->id)
            ->where('activo', true)
            ->where('aprobado', true)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function productosDelMismoVendedor($limit = 6)
    {
        return self::where('vendedor_id', $this->vendedor_id)
            ->where('id', '!=', $this->id)
            ->where('activo', true)
            ->where('aprobado', true)
            ->limit($limit)
            ->get();
    }

    public function estaEnWishlist($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return false;
        }
        
        return WishlistItem::whereHas('wishlist', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('producto_id', $this->id)->exists();
    }

    public function obtenerEstadisticasVentas($periodo = '30dias')
    {
        $fechaInicio = now()->subDays(30);
        
        if ($periodo === '7dias') {
            $fechaInicio = now()->subDays(7);
        } elseif ($periodo === '90dias') {
            $fechaInicio = now()->subDays(90);
        } elseif ($periodo === 'anio') {
            $fechaInicio = now()->subYear();
        }
        
        $ventas = $this->pedidoItems()
            ->whereHas('pedido', function($query) use ($fechaInicio) {
                $query->where('created_at', '>=', $fechaInicio)
                      ->where('estado_pedido', 'entregado');
            })
            ->selectRaw('DATE(pedido_items.created_at) as fecha, SUM(cantidad) as cantidad, SUM(subtotal) as monto')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
            
        return [
            'total_ventas' => $ventas->sum('cantidad'),
            'total_monto' => $ventas->sum('monto'),
            'ventas_por_dia' => $ventas,
            'promedio_diario' => $ventas->count() > 0 ? $ventas->sum('cantidad') / $ventas->count() : 0
        ];
    }

    // SCOPE
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->where('aprobado', true);
    }

    public function scopeEnOferta($query)
    {
        return $query->where('oferta', true)->whereNotNull('precio_descuento');
    }

    public function scopeDestacados($query)
    {
        return $query->where('destacado', true);
    }

    public function scopeNuevos($query)
    {
        return $query->where('nuevo', true);
    }

    public function scopeConEnvioGratis($query)
    {
        return $query->where('envio_gratis', true);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('descripcion', 'LIKE', "%{$termino}%")
              ->orWhere('marca', 'LIKE', "%{$termino}%")
              ->orWhere('modelo', 'LIKE', "%{$termino}%")
              ->orWhere('codigo', 'LIKE', "%{$termino}%");
        });
    }

    public function scopeFiltrarPorPrecio($query, $min, $max)
    {
        return $query->whereBetween('precio', [$min, $max]);
    }

    public function scopeOrdenarPor($query, $orden)
    {
        switch ($orden) {
            case 'precio_asc':
                return $query->orderBy('precio');
            case 'precio_desc':
                return $query->orderBy('precio', 'desc');
            case 'mas_vendidos':
                return $query->orderBy('ventas', 'desc');
            case 'mejor_calificados':
                return $query->orderBy('calificacion_promedio', 'desc');
            case 'nuevos':
                return $query->orderBy('created_at', 'desc');
            case 'nombre_asc':
                return $query->orderBy('nombre');
            case 'nombre_desc':
                return $query->orderBy('nombre', 'desc');
            default:
                return $query->orderBy('created_at', 'desc');
        }
    }
}