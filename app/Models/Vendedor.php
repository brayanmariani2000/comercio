<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Vendedor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rif',
        'razon_social',
        'nombre_comercial',
        'direccion_fiscal',
        'telefono',
        'email',
        'ciudad',
        'estado',
        'tipo_vendedor',
        'calificacion_promedio',
        'total_ventas',
        'verificado',
        'activo',
        'metodos_pago',
        'zonas_envio',
        'descripcion',
        'logo',
        'banner',
        'politica_devoluciones',
        'politica_garantias',
        'tiempo_respuesta_promedio',
        'porcentaje_envios_completados',
        'membresia',
        'fecha_vencimiento_membresia',
    ];

    protected $casts = [
        'metodos_pago' => 'array',
        'zonas_envio' => 'array',
        'calificacion_promedio' => 'decimal:2',
        'total_ventas' => 'integer',
        'verificado' => 'boolean',
        'activo' => 'boolean',
        'tiempo_respuesta_promedio' => 'integer',
        'porcentaje_envios_completados' => 'decimal:2',
        'fecha_vencimiento_membresia' => 'date',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function productosActivos()
    {
        return $this->hasMany(Producto::class)->where('activo', true)->where('aprobado', true);
    }

    public function productosDestacados()
    {
        return $this->productosActivos()->where('destacado', true);
    }

    public function productosEnOferta()
    {
        return $this->productosActivos()->where('oferta', true);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function pedidosPendientes()
    {
        return $this->pedidos()->whereIn('estado_pedido', ['pendiente', 'confirmado', 'preparando']);
    }

    public function pedidosCompletados()
    {
        return $this->pedidos()->where('estado_pedido', 'entregado');
    }

    public function conversaciones()
    {
        return $this->hasMany(Conversacion::class);
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    public function comisiones()
    {
        return $this->hasMany(Comision::class);
    }

    public function pagos()
    {
        return $this->hasMany(PagoVendedor::class);
    }

    public function reclamos()
    {
        return $this->hasManyThrough(Reclamo::class, Pedido::class);
    }

    public function resenas()
    {
        return $this->hasManyThrough(Resena::class, Producto::class);
    }

    // MÉTODOS
    public function getLogoUrlAttribute()
    {
        return $this->logo ? Storage::url($this->logo) : asset('images/default-vendedor.jpg');
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner ? Storage::url($this->banner) : null;
    }

    public function calcularRatingPromedio()
    {
        $promedio = $this->resenas()->avg('calificacion');
        $this->calificacion_promedio = $promedio ?? 0;
        $this->save();
        
        return $this->calificacion_promedio;
    }

    public function actualizarEstadisticas()
    {
        $this->total_ventas = $this->pedidosCompletados()->count();
        
        // Calcular tiempo de respuesta promedio
        $tiempoRespuesta = $this->mensajes()
            ->whereNotNull('created_at')
            ->avg(\DB::raw('TIMESTAMPDIFF(MINUTE, created_at, updated_at)'));
            
        $this->tiempo_respuesta_promedio = $tiempoRespuesta ?? 0;
        
        // Calcular porcentaje de envíos completados
        $totalPedidos = $this->pedidos()->count();
        $pedidosEntregados = $this->pedidosCompletados()->count();
        $this->porcentaje_envios_completados = $totalPedidos > 0 
            ? ($pedidosEntregados / $totalPedidos) * 100 
            : 0;
            
        $this->save();
    }

    public function puedePublicarProducto()
    {
        if (!$this->activo || !$this->verificado) {
            return false;
        }
        
        // Verificar límite de productos según membresía
        $limite = $this->obtenerLimiteProductos();
        if ($limite > 0 && $this->productos()->count() >= $limite) {
            return false;
        }
        
        // Verificar membresía activa
        if ($this->fecha_vencimiento_membresia && $this->fecha_vencimiento_membresia->isPast()) {
            return false;
        }
        
        return true;
    }

    public function obtenerLimiteProductos()
    {
        $planes = [
            'basico' => 50,
            'profesional' => 200,
            'premium' => 1000,
            'ilimitado' => 0, // 0 = ilimitado
        ];
        
        return $planes[$this->membresia] ?? 10;
    }

    public function tieneMembresiaActiva()
    {
        return !$this->fecha_vencimiento_membresia || 
               $this->fecha_vencimiento_membresia->isFuture();
    }

    public function agregarMetodoPago($metodo)
    {
        $metodos = $this->metodos_pago ?? [];
        if (!in_array($metodo, $metodos)) {
            $metodos[] = $metodo;
            $this->metodos_pago = $metodos;
            $this->save();
        }
    }

    public function agregarZonaEnvio($estado, $ciudad = null, $costo = 0)
    {
        $zonas = $this->zonas_envio ?? [];
        $zonaKey = $ciudad ? "{$estado}_{$ciudad}" : $estado;
        
        if (!isset($zonas[$zonaKey])) {
            $zonas[$zonaKey] = [
                'estado' => $estado,
                'ciudad' => $ciudad,
                'costo' => $costo,
                'activo' => true
            ];
            $this->zonas_envio = $zonas;
            $this->save();
        }
    }

    public function calcularComisionPedido($pedido)
    {
        // Porcentaje de comisión según membresía
        $comisiones = [
            'basico' => 15, // 15%
            'profesional' => 12,
            'premium' => 8,
            'ilimitado' => 5,
        ];
        
        $porcentaje = $comisiones[$this->membresia] ?? 15;
        $montoComision = ($pedido->total * $porcentaje) / 100;
        
        return [
            'porcentaje' => $porcentaje,
            'monto' => $montoComision,
            'monto_vendedor' => $pedido->total - $montoComision
        ];
    }

    public function generarReporteVentas($fechaInicio, $fechaFin)
    {
        $pedidos = $this->pedidos()
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->get();
            
        $totalVentas = $pedidos->sum('total');
        $totalPedidos = $pedidos->count();
        $pedidosCompletados = $pedidos->where('estado_pedido', 'entregado')->count();
        
        return [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ],
            'total_ventas' => $totalVentas,
            'total_pedidos' => $totalPedidos,
            'pedidos_completados' => $pedidosCompletados,
            'tasa_completacion' => $totalPedidos > 0 ? ($pedidosCompletados / $totalPedidos) * 100 : 0,
            'ventas_por_dia' => $pedidos->groupBy(function($pedido) {
                return $pedido->created_at->format('Y-m-d');
            })->map(function($grupo) {
                return $grupo->sum('total');
            }),
            'productos_mas_vendidos' => $this->productos()
                ->whereHas('pedidoItems', function($query) use ($fechaInicio, $fechaFin) {
                    $query->whereHas('pedido', function($q) use ($fechaInicio, $fechaFin) {
                        $q->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                    });
                })
                ->withCount(['pedidoItems as cantidad_vendida' => function($query) use ($fechaInicio, $fechaFin) {
                    $query->whereHas('pedido', function($q) use ($fechaInicio, $fechaFin) {
                        $q->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                    });
                }])
                ->orderBy('cantidad_vendida', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    // SCOPE
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeVerificados($query)
    {
        return $query->where('verificado', true);
    }

    public function scopeConMejorRating($query, $limit = 10)
    {
        return $query->orderBy('calificacion_promedio', 'desc')->limit($limit);
    }

    public function scopeConMasVentas($query, $limit = 10)
    {
        return $query->orderBy('total_ventas', 'desc')->limit($limit);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}