<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Mensaje extends Model
{
    use HasFactory;

    protected $table = 'mensajes';

    protected $fillable = [
        'conversacion_id',
        'user_id',
        'vendedor_id',
        'mensaje',
        'adjuntos',
        'leido',
        'leido_at',
        'tipo',
        'sistema',
        'referencia_pedido_id',
        'referencia_producto_id',
    ];

    protected $casts = [
        'adjuntos' => 'array',
        'leido' => 'boolean',
        'leido_at' => 'datetime',
        'sistema' => 'boolean',
    ];

    // RELACIONES
    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function referenciaPedido()
    {
        return $this->belongsTo(Pedido::class, 'referencia_pedido_id');
    }

    public function referenciaProducto()
    {
        return $this->belongsTo(Producto::class, 'referencia_producto_id');
    }

    // MÉTODOS
    public function getRemitenteAttribute()
    {
        if ($this->user_id) {
            return [
                'tipo' => 'usuario',
                'id' => $this->user_id,
                'nombre' => $this->user->name,
                'avatar' => $this->user->avatar,
            ];
        } elseif ($this->vendedor_id) {
            return [
                'tipo' => 'vendedor',
                'id' => $this->vendedor_id,
                'nombre' => $this->vendedor->nombre_comercial,
                'avatar' => $this->vendedor->logo,
            ];
        } else {
            return [
                'tipo' => 'sistema',
                'nombre' => 'Sistema',
                'avatar' => null,
            ];
        }
    }

    public function esDelUsuario()
    {
        return !is_null($this->user_id);
    }

    public function esDelVendedor()
    {
        return !is_null($this->vendedor_id);
    }

    public function esDelSistema()
    {
        return $this->sistema || (is_null($this->user_id) && is_null($this->vendedor_id));
    }

    public function marcarComoLeido()
    {
        if (!$this->leido) {
            $this->leido = true;
            $this->leido_at = now();
            $this->save();
        }
        
        return $this;
    }

    public function obtenerAdjuntosUrls()
    {
        if (empty($this->adjuntos)) {
            return [];
        }
        
        return array_map(function($adjunto) {
            return [
                'nombre' => $adjunto['nombre'] ?? 'archivo',
                'url' => Storage::url($adjunto['path'] ?? ''),
                'tipo' => $adjunto['tipo'] ?? 'archivo',
                'tamaño' => $adjunto['tamaño'] ?? null,
            ];
        }, $this->adjuntos);
    }

    // SCOPE
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    public function scopeLeidos($query)
    {
        return $query->where('leido', true);
    }

    public function scopePorConversacion($query, $conversacionId)
    {
        return $query->where('conversacion_id', $conversacionId);
    }

    public function scopeRecientes($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeConAdjuntos($query)
    {
        return $query->whereNotNull('adjuntos')->where('adjuntos', '!=', '[]');
    }

    public function scopeSistema($query)
    {
        return $query->where('sistema', true);
    }
}
