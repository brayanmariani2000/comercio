<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_items';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'vendedor_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'comision_plataforma',
        'nombre_producto',
        'opciones',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'comision_plataforma' => 'decimal:2',
        'opciones' => 'array',
    ];

    // RELACIONES
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    // MÉTODOS
    public function calcularSubtotal()
    {
        $subtotal = $this->cantidad * $this->precio_unitario;
        if ($this->descuento) {
            $subtotal -= $this->descuento;
        }
        return max(0, $subtotal);
    }

    public function calcularComision($porcentaje = 10)
    {
        return $this->subtotal * ($porcentaje / 100);
    }

    public function getTotalVendedorAttribute()
    {
        return $this->subtotal - $this->comision_plataforma;
    }

    public function getDescuentoPorcentajeAttribute()
    {
        if ($this->descuento && $this->precio_unitario > 0) {
            return round(($this->descuento / ($this->cantidad * $this->precio_unitario)) * 100, 2);
        }
        return 0;
    }

    // MÉTODOS ESTÁTICOS
    public static function crearDesdeCarritoItem($carritoItem, $pedidoId)
    {
        return self::create([
            'pedido_id' => $pedidoId,
            'producto_id' => $carritoItem->producto_id,
            'vendedor_id' => $carritoItem->producto->vendedor_id,
            'cantidad' => $carritoItem->cantidad,
            'precio_unitario' => $carritoItem->precio_unitario,
            'subtotal' => $carritoItem->calcularSubtotal(),
            'nombre_producto' => $carritoItem->producto->nombre,
            'opciones' => $carritoItem->opciones,
        ]);
    }

    // SCOPE
    public function scopeDelPedido($query, $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId);
    }

    public function scopeDelVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }
}
