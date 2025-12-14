<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    use HasFactory;

    protected $table = 'conversaciones';

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

