<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'minimo_compra',
        'usos_maximos',
        'usos_actuales',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'categorias_aplicables',
        'productos_aplicables',
        'usuarios_aplicables',
        'excluir_productos_oferta',
        'solo_primer_compra',
        'solo_usuarios_nuevos',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'minimo_compra' => 'decimal:2',
        'usos_maximos' => 'integer',
        'usos_actuales' => 'integer',
        'activo' => 'boolean',
        'categorias_aplicables' => 'array',
        'productos_aplicables' => 'array',
        'usuarios_aplicables' => 'array',
        'excluir_productos_oferta' => 'boolean',
        'solo_primer_compra' => 'boolean',
        'solo_usuarios_nuevos' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // RELACIONES
    public function usos()
    {
        return $this->hasMany(CuponUso::class);
    }

    // MÉTODOS
    public static function validar($codigo, $userId, $subtotal)
    {
        $cupon = self::where('codigo', $codigo)
            ->where('activo', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->first();
            
        if (!$cupon) {
            return null;
        }
        
        // Verificar usos máximos
        if ($cupon->usos_maximos && $cupon->usos_actuales >= $cupon->usos_maximos) {
            return null;
        }
        
        // Verificar mínimo de compra
        if ($cupon->minimo_compra && $subtotal < $cupon->minimo_compra) {
            return null;
        }
        
        // Verificar si es solo para primer compra
        if ($cupon->solo_primer_compra) {
            $comprasUsuario = Pedido::where('user_id', $userId)
                ->where('estado_pedido', 'entregado')
                ->count();
                
            if ($comprasUsuario > 0) {
                return null;
            }
        }
        
        // Verificar si es solo para usuarios nuevos
        if ($cupon->solo_usuarios_nuevos) {
            $usuario = User::find($userId);
            if ($usuario && $usuario->created_at->diffInDays(now()) > 30) {
                return null;
            }
        }
        
        // Verificar usuarios aplicables
        if ($cupon->usuarios_aplicables && 
            !in_array($userId, $cupon->usuarios_aplicables)) {
            return null;
        }
        
        return $cupon;
    }

    public function calcularDescuento($subtotal)
    {
        if ($this->tipo === 'porcentaje') {
            return ($subtotal * $this->valor) / 100;
        } elseif ($this->tipo === 'monto_fijo') {
            return min($this->valor, $subtotal); // No puede exceder el subtotal
        } elseif ($this->tipo === 'envio_gratis') {
            return 0; // El descuento se aplica al envío
        }
        
        return 0;
    }

    public function registrarUso($userId, $pedidoId, $descuentoAplicado)
    {
        $this->increment('usos_actuales');
        
        return $this->usos()->create([
            'user_id' => $userId,
            'pedido_id' => $pedidoId,
            'descuento_aplicado' => $descuentoAplicado,
        ]);
    }

    public function esValidoParaProducto($productoId)
    {
        if ($this->excluir_productos_oferta) {
            $producto = Producto::find($productoId);
            if ($producto && $producto->oferta) {
                return false;
            }
        }
        
        if ($this->productos_aplicables && 
            !in_array($productoId, $this->productos_aplicables)) {
            return false;
        }
        
        return true;
    }

    public function esValidoParaCategoria($categoriaId)
    {
        if ($this->categorias_aplicables && 
            !in_array($categoriaId, $this->categorias_aplicables)) {
            return false;
        }
        
        return true;
    }

    public function obtenerEstadisticas()
    {
        return [
            'total_usos' => $this->usos_actuales,
            'limite_usos' => $this->usos_maximos,
            'porcentaje_uso' => $this->usos_maximos ? ($this->usos_actuales / $this->usos_maximos) * 100 : 0,
            'monto_total_descuento' => $this->usos()->sum('descuento_aplicado'),
            'usos_por_dia' => $this->usos()
                ->selectRaw('DATE(created_at) as fecha, COUNT(*) as cantidad')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get(),
            'top_usuarios' => $this->usos()
                ->with('user')
                ->selectRaw('user_id, COUNT(*) as usos, SUM(descuento_aplicado) as total_descuento')
                ->groupBy('user_id')
                ->orderBy('usos', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    // SCOPE
    public function scopeActivos($query)
    {
        return $query->where('activo', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now());
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    public function scopeConUsosDisponibles($query)
    {
        return $query->where(function($q) {
            $q->whereNull('usos_maximos')
              ->orWhereRaw('usos_actuales < usos_maximos');
        });
    }
}