<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    use HasFactory;

    protected $table = 'resenas';

    protected $fillable = [
        'user_id',
        'producto_id',
        'pedido_id',
        'calificacion',
        'titulo',
        'comentario',
        'ventajas',
        'desventajas',
        'recomendado',
        'imagenes',
        'likes',
        'verificada_compra',
        'aprobada',
        'activa',
        'respuesta_vendedor',
        'fecha_respuesta_vendedor',
        'reportada',
        'motivo_reporte',
        'oculta',
    ];

    protected $casts = [
        'calificacion' => 'integer',
        'recomendado' => 'boolean',
        'imagenes' => 'array',
        'ventajas' => 'array',
        'desventajas' => 'array',
        'likes' => 'integer',
        'verificada_compra' => 'boolean',
        'aprobada' => 'boolean',
        'activa' => 'boolean',
        'reportada' => 'boolean',
        'oculta' => 'boolean',
        'fecha_respuesta_vendedor' => 'datetime',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function likesRelacion()
    {
        return $this->hasMany(ResenaLike::class);
    }

    // MÉTODOS
    public function getEstrellasAttribute()
    {
        return str_repeat('★', $this->calificacion) . str_repeat('☆', 5 - $this->calificacion);
    }

    public function esVerificada()
    {
        return $this->verificada_compra && $this->pedido_id;
    }

    public function agregarLike($userId)
    {
        $like = $this->likesRelacion()->where('user_id', $userId)->first();
        
        if ($like) {
            if ($like->like) {
                $like->delete();
                $this->decrement('likes');
            } else {
                $like->update(['like' => true]);
                $this->increment('likes', 2); // Suma 2 porque antes era dislike
            }
        } else {
            $this->likesRelacion()->create([
                'user_id' => $userId,
                'like' => true,
            ]);
            $this->increment('likes');
        }
        
        return $this->fresh()->likes;
    }

    public function agregarDislike($userId)
    {
        $like = $this->likesRelacion()->where('user_id', $userId)->first();
        
        if ($like) {
            if (!$like->like) {
                $like->delete();
                // No se decrementa likes porque los dislikes no afectan el contador
            } else {
                $like->update(['like' => false]);
                $this->decrement('likes');
            }
        } else {
            $this->likesRelacion()->create([
                'user_id' => $userId,
                'like' => false,
            ]);
            // Los dislikes no afectan el contador de likes
        }
        
        return $this->fresh()->likes;
    }

    public function responderComoVendedor($respuesta, $vendedorId)
    {
        $this->respuesta_vendedor = $respuesta;
        $this->fecha_respuesta_vendedor = now();
        $this->save();
        
        // Notificar al usuario
        $this->user->generarNotificacion(
            'Respuesta a tu reseña',
            "El vendedor ha respondido a tu reseña del producto {$this->producto->nombre}",
            'producto',
            ['producto_id' => $this->producto_id, 'resena_id' => $this->id]
        );
        
        return $this;
    }

    public function reportar($motivo, $userId)
    {
        $this->reportada = true;
        $this->motivo_reporte = $motivo;
        $this->save();
        
        // Notificar a administradores
        $administradores = User::whereIn('tipo_usuario', ['administrador', 'supervisor'])
            ->where('activo', true)
            ->get();
            
        foreach ($administradores as $admin) {
            $admin->generarNotificacion(
                'Reseña reportada',
                "La reseña del producto {$this->producto->nombre} ha sido reportada",
                'sistema',
                ['resena_id' => $this->id, 'motivo' => $motivo, 'reportado_por' => $userId]
            );
        }
        
        return $this;
    }

    public function aprobar()
    {
        $this->aprobada = true;
        $this->activa = true;
        $this->save();
        
        // Actualizar rating del producto
        $this->producto->actualizarRating();
        
        return $this;
    }

    public function rechazar($razon = null)
    {
        $this->aprobada = false;
        $this->activa = false;
        $this->save();
        
        // Notificar al usuario
        $this->user->generarNotificacion(
            'Reseña rechazada',
            "Tu reseña del producto {$this->producto->nombre} ha sido rechazada" . ($razon ? ": {$razon}" : ""),
            'producto',
            ['producto_id' => $this->producto_id]
        );
        
        return $this;
    }

    public function ocultar()
    {
        $this->oculta = true;
        $this->activa = false;
        $this->save();
        
        // Actualizar rating del producto
        $this->producto->actualizarRating();
        
        return $this;
    }

    public function obtenerEstadisticas()
    {
        $likes = $this->likesRelacion()->where('like', true)->count();
        $dislikes = $this->likesRelacion()->where('like', false)->count();
        $totalVotos = $likes + $dislikes;
        
        return [
            'likes' => $likes,
            'dislikes' => $dislikes,
            'total_votos' => $totalVotos,
            'porcentaje_util' => $totalVotos > 0 ? round(($likes / $totalVotos) * 100, 1) : 0,
            'dias_publicada' => $this->created_at->diffInDays(now()),
            'tiene_respuesta' => !empty($this->respuesta_vendedor),
        ];
    }

    // SCOPE
    public function scopeAprobadas($query)
    {
        return $query->where('aprobada', true)->where('activa', true);
    }

    public function scopeVerificadas($query)
    {
        return $query->where('verificada_compra', true);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorCalificacion($query, $calificacion)
    {
        return $query->where('calificacion', $calificacion);
    }

    public function scopeConImagenes($query)
    {
        return $query->whereNotNull('imagenes')->where('imagenes', '!=', '[]');
    }

    public function scopeConRespuesta($query)
    {
        return $query->whereNotNull('respuesta_vendedor');
    }

    public function scopeReportadas($query)
    {
        return $query->where('reportada', true);
    }

    public function scopeRecientes($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeMasUtiles($query, $limit = 10)
    {
        return $query->orderBy('likes', 'desc')->limit($limit);
    }
}

// Modelo ResenaLike
class ResenaLike extends Model
{
    use HasFactory;

    protected $table = 'resena_likes';

    protected $fillable = [
        'resena_id',
        'user_id',
        'like',
    ];

    protected $casts = [
        'like' => 'boolean',
    ];

    // RELACIONES
    public function resena()
    {
        return $this->belongsTo(Resena::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}