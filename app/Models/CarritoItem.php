<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarritoItem extends Model
{
    use HasFactory;

    protected $table = 'carrito_items';

    protected $fillable = [
        'carrito_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'opciones',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'opciones' => 'array',
    ];

    // RELACIONES
    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // MÉTODOS
    public function calcularSubtotal()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    public function getSubtotalAttribute()
    {
        return $this->calcularSubtotal();
    }

    public function incrementarCantidad($cantidad = 1)
    {
        $this->cantidad += $cantidad;
        $this->save();
    }

    public function decrementarCantidad($cantidad = 1)
    {
        if ($this->cantidad > $cantidad) {
            $this->cantidad -= $cantidad;
            $this->save();
        } else {
            $this->delete();
        }
    }

    public function actualizarCantidad($cantidad)
    {
        if ($cantidad <= 0) {
            $this->delete();
        } else {
            $this->cantidad = $cantidad;
            $this->save();
        }
    }

    public function stockDisponible()
    {
        return $this->producto && $this->producto->stock >= $this->cantidad;
    }

    public function productoActivo()
    {
        return $this->producto && $this->producto->activo;
    }

    // SCOPE
    public function scopeConProducto($query)
    {
        return $query->with(['producto' => function($q) {
            $q->with(['imagenes', 'vendedor']);
        }]);
    }
}
