<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendedor_id',
        'producto_id',
        'pedido_id',
        'asunto',
        'estado',
        'ultimo_mensaje_at',
        'cerrada_por',
        'motivo_cierre',
        'fecha_cierre',
        'etiquetas',
        'prioridad',
    ];

    protected $casts = [
        'ultimo_mensaje_at' => 'datetime',
        'fecha_cierre' => 'datetime',
        'etiquetas' => 'array',
        'prioridad' => 'integer',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    public function ultimoMensaje()
    {
        return $this->hasOne(Mensaje::class)->latest();
    }

    public function mensajesNoLeidos()
    {
        return $this->hasMany(Mensaje::class)->where('leido', false);
    }

    // MÉTODOS
    public function getTituloAttribute()
    {
        if ($this->asunto) {
            return $this->asunto;
        }
        
        if ($this->producto) {
            return "Consulta sobre: {$this->producto->nombre}";
        }
        
        if ($this->pedido) {
            return "Pedido: {$this->pedido->numero_pedido}";
        }
        
        return "Conversación con " . ($this->vendedor->nombre_comercial ?? 'Vendedor');
    }

    public function getInterlocutorAttribute()
    {
        if (auth()->check()) {
            if (auth()->id() === $this->user_id) {
                return $this->vendedor;
            } else {
                return $this->user;
            }
        }
        
        return $this->user_id === optional(auth()->user())->id ? $this->vendedor : $this->user;
    }

    public function tieneAcceso($userId)
    {
        return $this->user_id === $userId || 
               ($this->vendedor && $this->vendedor->user_id === $userId) ||
               auth()->user()->esAdministrador();
    }

    public function enviarMensaje($contenido, $userId = null, $vendedorId = null, $adjuntos = null)
    {
        $mensaje = $this->mensajes()->create([
            'user_id' => $userId,
            'vendedor_id' => $vendedorId,
            'mensaje' => $contenido,
            'adjuntos' => $adjuntos,
            'leido' => false,
        ]);
        
        $this->ultimo_mensaje_at = now();
        $this->save();
        
        // Generar notificación
        $this->generarNotificacionNuevoMensaje($mensaje);
        
        return $mensaje;
    }

    public function generarNotificacionNuevoMensaje($mensaje)
    {
        $destinatario = null;
        $titulo = 'Nuevo mensaje';
        
        if ($mensaje->user_id) {
            // Mensaje del comprador, notificar al vendedor
            $destinatario = $this->vendedor->user;
            $titulo = "Nuevo mensaje de {$this->user->name}";
        } elseif ($mensaje->vendedor_id) {
            // Mensaje del vendedor, notificar al comprador
            $destinatario = $this->user;
            $titulo = "Nuevo mensaje de {$this->vendedor->nombre_comercial}";
        }
        
        if ($destinatario) {
            $destinatario->generarNotificacion(
                $titulo,
                substr($mensaje->mensaje, 0, 100) . '...',
                'mensaje',
                [
                    'conversacion_id' => $this->id,
                    'mensaje_id' => $mensaje->id,
                    'asunto' => $this->titulo,
                ]
            );
        }
    }

    public function marcarComoLeida($userId = null, $vendedorId = null)
    {
        $query = $this->mensajes()->where('leido', false);
        
        if ($userId) {
            $query->where('user_id', '!=', $userId);
        }
        
        if ($vendedorId) {
            $query->where('vendedor_id', '!=', $vendedorId);
        }
        
        $query->update([
            'leido' => true,
            'leido_at' => now(),
        ]);
        
        return $this;
    }

    public function cerrar($userId, $motivo = null)
    {
        $this->estado = 'cerrada';
        $this->cerrada_por = $userId;
        $this->motivo_cierre = $motivo;
        $this->fecha_cierre = now();
        $this->save();
        
        // Notificar al otro participante
        $otroUsuario = $this->user_id === $userId ? $this->vendedor->user : $this->user;
        
        if ($otroUsuario) {
            $otroUsuario->generarNotificacion(
                'Conversación cerrada',
                "La conversación '{$this->titulo}' ha sido cerrada" . ($motivo ? ": {$motivo}" : ""),
                'mensaje',
                ['conversacion_id' => $this->id]
            );
        }
        
        return $this;
    }

    public function reabrir()
    {
        $this->estado = 'abierta';
        $this->cerrada_por = null;
        $this->motivo_cierre = null;
        $this->fecha_cierre = null;
        $this->save();
        
        return $this;
    }

    public function agregarEtiqueta($etiqueta)
    {
        $etiquetas = $this->etiquetas ?? [];
        
        if (!in_array($etiqueta, $etiquetas)) {
            $etiquetas[] = $etiqueta;
            $this->etiquetas = $etiquetas;
            $this->save();
        }
        
        return $this;
    }

    public function eliminarEtiqueta($etiqueta)
    {
        $etiquetas = $this->etiquetas ?? [];
        
        $key = array_search($etiqueta, $etiquetas);
        if ($key !== false) {
            unset($etiquetas[$key]);
            $this->etiquetas = array_values($etiquetas);
            $this->save();
        }
        
        return $this;
    }

    public function calcularTiempoRespuestaPromedio()
    {
        $mensajesVendedor = $this->mensajes()->whereNotNull('vendedor_id')->get();
        
        if ($mensajesVendedor->isEmpty()) {
            return null;
        }
        
        $totalSegundos = 0;
        $contador = 0;
        
        foreach ($mensajesVendedor as $mensaje) {
            $mensajeAnterior = $this->mensajes()
                ->where('created_at', '<', $mensaje->created_at)
                ->whereNull('vendedor_id')
                ->latest()
                ->first();
                
            if ($mensajeAnterior) {
                $diferencia = $mensaje->created_at->diffInSeconds($mensajeAnterior->created_at);
                $totalSegundos += $diferencia;
                $contador++;
            }
        }
        
        return $contador > 0 ? round($totalSegundos / $contador) : null;
    }

    // SCOPE
    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierta');
    }

    public function scopeCerradas($query)
    {
        return $query->where('estado', 'cerrada');
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopeConMensajesNoLeidos($query, $userId = null, $vendedorId = null)
    {
        return $query->whereHas('mensajes', function($q) use ($userId, $vendedorId) {
            $q->where('leido', false);
            
            if ($userId) {
                $q->where('user_id', '!=', $userId);
            }
            
            if ($vendedorId) {
                $q->where('vendedor_id', '!=', $vendedorId);
            }
        });
    }

    public function scopeRecientes($query, $limit = 20)
    {
        return $query->orderBy('ultimo_mensaje_at', 'desc')->limit($limit);
    }

    public function scopeConEtiqueta($query, $etiqueta)
    {
        return $query->whereJsonContains('etiquetas', $etiqueta);
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }
}

// Modelo Mensaje
class Mensaje extends Model
{
    use HasFactory;

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
                'url' => Storage::url($adjunto['path']),
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