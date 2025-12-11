<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CarritoItem::class);
    }

    // MÉTODOS
    public function agregarProducto($productoId, $cantidad = 1)
    {
        $producto = Producto::findOrFail($productoId);
        
        // Verificar stock
        if ($producto->stock < $cantidad) {
            throw new \Exception('Stock insuficiente');
        }
        
        // Verificar si ya existe en el carrito
        $itemExistente = $this->items()->where('producto_id', $productoId)->first();
        
        if ($itemExistente) {
            // Actualizar cantidad
            $nuevaCantidad = $itemExistente->cantidad + $cantidad;
            
            if ($producto->stock < $nuevaCantidad) {
                throw new \Exception('Stock insuficiente para la cantidad solicitada');
            }
            
            $itemExistente->update([
                'cantidad' => $nuevaCantidad,
                'precio_unitario' => $producto->precio_actual,
                'subtotal' => $nuevaCantidad * $producto->precio_actual,
            ]);
            
            return $itemExistente;
        } else {
            // Crear nuevo item
            return $this->items()->create([
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio_actual,
                'subtotal' => $cantidad * $producto->precio_actual,
            ]);
        }
    }

    public function actualizarCantidad($productoId, $cantidad)
    {
        $producto = Producto::findOrFail($productoId);
        
        if ($producto->stock < $cantidad) {
            throw new \Exception('Stock insuficiente');
        }
        
        $item = $this->items()->where('producto_id', $productoId)->firstOrFail();
        
        $item->update([
            'cantidad' => $cantidad,
            'precio_unitario' => $producto->precio_actual,
            'subtotal' => $cantidad * $producto->precio_actual,
        ]);
        
        return $item;
    }

    public function eliminarProducto($productoId)
    {
        return $this->items()->where('producto_id', $productoId)->delete();
    }

    public function vaciar()
    {
        return $this->items()->delete();
    }

    public function calcularSubtotal()
    {
        return $this->items->sum('subtotal');
    }

    public function calcularTotalItems()
    {
        return $this->items->sum('cantidad');
    }

    public function calcularEnvio($metodoEnvioId, $datos = [])
    {
        $metodoEnvio = MetodoEnvio::find($metodoEnvioId);
        
        if (!$metodoEnvio) {
            return 0;
        }
        
        return $metodoEnvio->calcularCosto($this->calcularSubtotal(), $datos);
    }

    public function aplicarCupon($codigoCupon)
    {
        $cupon = Cupon::validar($codigoCupon, $this->user_id, $this->calcularSubtotal());
        
        if (!$cupon) {
            throw new \Exception('Cupón inválido o expirado');
        }
        
        return [
            'cupon' => $cupon,
            'descuento' => $cupon->calcularDescuento($this->calcularSubtotal()),
            'subtotal_con_descuento' => $this->calcularSubtotal() - $cupon->calcularDescuento($this->calcularSubtotal())
        ];
    }

    public function verificarDisponibilidad()
    {
        $productosNoDisponibles = [];
        
        foreach ($this->items as $item) {
            if ($item->producto->stock < $item->cantidad) {
                $productosNoDisponibles[] = [
                    'producto' => $item->producto,
                    'cantidad_solicitada' => $item->cantidad,
                    'stock_disponible' => $item->producto->stock
                ];
            }
        }
        
        return [
            'disponible' => empty($productosNoDisponibles),
            'productos_no_disponibles' => $productosNoDisponibles
        ];
    }

    public function agruparPorVendedor()
    {
        return $this->items->groupBy(function($item) {
            return $item->producto->vendedor_id;
        })->map(function($items, $vendedorId) {
            $vendedor = Vendedor::find($vendedorId);
            return [
                'vendedor' => $vendedor,
                'items' => $items,
                'subtotal' => $items->sum('subtotal'),
                'cantidad_items' => $items->sum('cantidad'),
            ];
        });
    }
}