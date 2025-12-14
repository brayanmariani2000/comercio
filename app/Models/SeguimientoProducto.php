<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoProducto extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_productos';

    protected $fillable = [
        'user_id',
        'producto_id',
        'precio_deseado',
        'notificar_disponibilidad',
        'notificar_precio',
        'activo',
    ];

    protected $casts = [
        'precio_deseado' => 'decimal:2',
        'notificar_disponibilidad' => 'boolean',
        'notificar_precio' => 'boolean',
        'activo' => 'boolean',
    ];

    // RELACIONES
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // MÉTODOS
    public function verificarYNotificar()
    {
        if (!$this->activo) {
            return false;
        }

        $producto = $this->producto;
        $debeNotificar = false;
        $mensaje = '';

        // Verificar disponibilidad
        if ($this->notificar_disponibilidad && $producto->stock > 0 && $producto->activo) {
            $debeNotificar = true;
            $mensaje = "El producto '{$producto->nombre}' está disponible nuevamente.";
        }

        // Verificar precio
        if ($this->notificar_precio && $this->precio_deseado) {
            $precioActual = $producto->precio_oferta ?? $producto->precio;
            if ($precioActual <= $this->precio_deseado) {
                $debeNotificar = true;
                $mensaje = "El producto '{$producto->nombre}' alcanzó tu precio deseado de Bs {$this->precio_deseado}.";
            }
        }

        if ($debeNotificar) {
            $this->usuario->generarNotificacion(
                'Alerta de Producto',
                $mensaje,
                'seguimiento_producto',
                ['producto_id' => $this->producto_id]
            );
        }

        return $debeNotificar;
    }

    public function desactivar()
    {
        $this->activo = false;
        $this->save();
    }

    public function activar()
    {
        $this->activo = true;
        $this->save();
    }

    // SCOPE
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDelProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeConNotificacionPrecio($query)
    {
        return $query->where('notificar_precio', true);
    }

    public function scopeConNotificacionDisponibilidad($query)
    {
        return $query->where('notificar_disponibilidad', true);
    }
}
