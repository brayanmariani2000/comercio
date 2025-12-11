<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'producto_id',
        'pregunta',
        'anonima',
        'vistas',
        'relevante',
        'reportada',
        'activa',
    ];

    protected $casts = [
        'anonima' => 'boolean',
        'vistas' => 'integer',
        'relevante' => 'boolean',
        'reportada' => 'boolean',
        'activa' => 'boolean',
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

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class);
    }

    public function respuestaOficial()
    {
        return $this->hasOne(Respuesta::class)->where('oficial', true);
    }

    public function votos()
    {
        return $this->hasMany(PreguntaVoto::class);
    }

    // MÉTODOS
    public function getNombreUsuarioAttribute()
    {
        if ($this->anonima) {
            return 'Usuario Anónimo';
        }
        
        return $this->user ? $this->user->name : 'Usuario';
    }

    public function incrementarVistas()
    {
        $this->increment('vistas');
        return $this;
    }

    public function agregarVoto($userId, $util = true)
    {
        $voto = $this->votos()->where('user_id', $userId)->first();
        
        if ($voto) {
            $voto->update(['util' => $util]);
        } else {
            $this->votos()->create([
                'user_id' => $userId,
                'util' => $util,
            ]);
        }
        
        return $this;
    }

    public function obtenerEstadisticasVotos()
    {
        $votosUtiles = $this->votos()->where('util', true)->count();
        $votosNoUtiles = $this->votos()->where('util', false)->count();
        $totalVotos = $votosUtiles + $votosNoUtiles;
        
        return [
            'utiles' => $votosUtiles,
            'no_utiles' => $votosNoUtiles,
            'total' => $totalVotos,
            'porcentaje_util' => $totalVotos > 0 ? round(($votosUtiles / $totalVotos) * 100, 1) : 0,
        ];
    }

    public function tieneRespuesta()
    {
        return $this->respuestas()->count() > 0;
    }

    public function tieneRespuestaOficial()
    {
        return $this->respuestaOficial()->exists();
    }

    public function responder($respuesta, $userId = null, $vendedorId = null, $oficial = false)
    {
        return $this->respuestas()->create([
            'user_id' => $userId,
            'vendedor_id' => $vendedorId,
            'respuesta' => $respuesta,
            'oficial' => $oficial,
        ]);
    }

    public function marcarComoRelevante()
    {
        $this->relevante = true;
        $this->save();
        
        return $this;
    }

    public function reportar($motivo = null)
    {
        $this->reportada = true;
        $this->save();
        
        // Notificar a administradores
        User::whereIn('tipo_usuario', ['administrador', 'supervisor'])
            ->where('activo', true)
            ->each(function($admin) use ($motivo) {
                $admin->generarNotificacion(
                    'Pregunta reportada',
                    "Una pregunta ha sido reportada" . ($motivo ? ": {$motivo}" : ""),
                    'sistema',
                    ['pregunta_id' => $this->id, 'producto_id' => $this->producto_id]
                );
            });
        
        return $this;
    }

    public function desactivar()
    {
        $this->activa = false;
        $this->save();
        
        return $this;
    }

    // SCOPE
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeRespondidas($query)
    {
        return $query->whereHas('respuestas');
    }

    public function scopeSinResponder($query)
    {
        return $query->whereDoesntHave('respuestas');
    }

    public function scopeRelevantes($query)
    {
        return $query->where('relevante', true);
    }

    public function scopeRecientes($query, $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeMasVotadas($query, $limit = 10)
    {
        return $query->withCount(['votos as votos_utiles' => function($q) {
            $q->where('util', true);
        }])->orderBy('votos_utiles', 'desc')->limit($limit);
    }

    public function scopeMasVistas($query, $limit = 10)
    {
        return $query->orderBy('vistas', 'desc')->limit($limit);
    }
}

// Modelo Respuesta
class Respuesta extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregunta_id',
        'user_id',
        'vendedor_id',
        'respuesta',
        'oficial',
        'likes',
        'reportada',
    ];

    protected $casts = [
        'oficial' => 'boolean',
        'likes' => 'integer',
        'reportada' => 'boolean',
    ];

    // RELACIONES
    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    // MÉTODOS
    public function getNombreRespondedorAttribute()
    {
        if ($this->vendedor) {
            return $this->vendedor->nombre_comercial . ' (Vendedor)';
        }
        
        if ($this->user) {
            return $this->user->name;
        }
        
        return 'Usuario';
    }

    public function esDelVendedor()
    {
        return !is_null($this->vendedor_id);
    }

    public function agregarLike()
    {
        $this->increment('likes');
        return $this;
    }

    public function reportar($motivo = null)
    {
        $this->reportada = true;
        $this->save();
        
        return $this;
    }

    // SCOPE
    public function scopeOficiales($query)
    {
        return $query->where('oficial', true);
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }
}

// Modelo PreguntaVoto
class PreguntaVoto extends Model
{
    use HasFactory;

    protected $table = 'pregunta_votos';

    protected $fillable = [
        'pregunta_id',
        'user_id',
        'util',
    ];

    protected $casts = [
        'util' => 'boolean',
    ];

    // RELACIONES
    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}