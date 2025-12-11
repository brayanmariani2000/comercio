<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cedula',
        'rif',
        'tipo_persona',
        'telefono',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'estado_id',
        'ciudad_id',
        'codigo_postal',
        'avatar',
        'tipo_usuario',
        'rating_promedio',
        'total_compras',
        'monto_total_compras',
        'verificado',
        'suspendido',
        'ultimo_acceso',
        'preferencias',
        'metodos_pago_guardados',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'fecha_nacimiento' => 'date',
        'preferencias' => 'array',
        'metodos_pago_guardados' => 'array',
        'rating_promedio' => 'decimal:2',
        'monto_total_compras' => 'decimal:2',
        'ultimo_acceso' => 'datetime',
    ];

    // RELACIONES
    public function estado()
    {
        return $this->belongsTo(EstadoVenezuela::class, 'estado_id');
    }

    public function ciudad()
    {
        return $this->belongsTo(CiudadVenezuela::class, 'ciudad_id');
    }

    public function direccionesEnvio()
    {
        return $this->hasMany(DireccionEnvio::class);
    }

    public function direccionPrincipal()
    {
        return $this->hasOne(DireccionEnvio::class)->where('principal', true);
    }

    public function vendedor()
    {
        return $this->hasOne(Vendedor::class);
    }

    public function carrito()
    {
        return $this->hasOne(Carrito::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistDefault()
    {
        return $this->hasOne(Wishlist::class)->where('predeterminada', true);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function pedidosCompletados()
    {
        return $this->hasMany(Pedido::class)->where('estado_pedido', 'entregado');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class);
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    public function notificacionesNoLeidas()
    {
        return $this->hasMany(Notificacion::class)->where('leida', false);
    }

    public function conversacionesComoComprador()
    {
        return $this->hasMany(Conversacion::class);
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    public function seguimientosProductos()
    {
        return $this->hasMany(SeguimientoProducto::class);
    }

    public function cuponesUsados()
    {
        return $this->hasMany(CuponUso::class);
    }

    public function reclamos()
    {
        return $this->hasMany(Reclamo::class);
    }

    // MÉTODOS
    public function esVendedor()
    {
        return $this->tipo_usuario === 'vendedor' || $this->vendedor()->exists();
    }

    public function esAdministrador()
    {
        return $this->tipo_usuario === 'administrador';
    }

    public function esSupervisor()
    {
        return $this->tipo_usuario === 'supervisor';
    }

    public function esComprador()
    {
        return $this->tipo_usuario === 'comprador';
    }

    public function puedeVender()
    {
        return $this->esVendedor() && $this->vendedor->activo && $this->vendedor->verificado;
    }

    public function tieneVendedorVerificado()
    {
        return $this->vendedor && $this->vendedor->verificado;
    }

    public function actualizarEstadisticas()
    {
        $this->total_compras = $this->pedidosCompletados()->count();
        $this->monto_total_compras = $this->pedidosCompletados()->sum('total');
        
        // Calcular rating promedio basado en reseñas recibidas como vendedor
        if ($this->esVendedor() && $this->vendedor) {
            $this->rating_promedio = $this->vendedor->calcularRatingPromedio();
        }
        
        $this->save();
    }

    public function agregarProductoACarrito($productoId, $cantidad = 1)
    {
        $carrito = $this->carrito ?? $this->carrito()->create();
        
        return $carrito->agregarProducto($productoId, $cantidad);
    }

    public function agregarProductoAWishlist($productoId, $wishlistId = null)
    {
        $wishlist = $wishlistId 
            ? $this->wishlists()->find($wishlistId)
            : $this->wishlistDefault;
            
        if (!$wishlist) {
            $wishlist = $this->wishlists()->create([
                'nombre' => 'Mi lista de deseos',
                'predeterminada' => true
            ]);
        }
        
        return $wishlist->agregarProducto($productoId);
    }

    public function crearPedido($datosPedido)
    {
        $carrito = $this->carrito;
        if (!$carrito || $carrito->items()->count() === 0) {
            throw new \Exception('El carrito está vacío');
        }
        
        return Pedido::crearDesdeCarrito($this, $carrito, $datosPedido);
    }

    public function generarNotificacion($titulo, $mensaje, $tipo = 'sistema', $data = null)
    {
        return $this->notificaciones()->create([
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'data' => $data,
            'leida' => false
        ]);
    }

    // SCOPE
    public function scopeCompradores($query)
    {
        return $query->where('tipo_usuario', 'comprador');
    }

    public function scopeVendedores($query)
    {
        return $query->where('tipo_usuario', 'vendedor');
    }

    public function scopeVerificados($query)
    {
        return $query->where('verificado', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('suspendido', false);
    }

    public function scopeConMasCompras($query, $limit = 10)
    {
        return $query->orderBy('total_compras', 'desc')->limit($limit);
    }
}