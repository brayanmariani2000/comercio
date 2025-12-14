<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiudadVenezuela extends Model
{
    use HasFactory;

    protected $table = 'ciudades_venezuela';

    protected $fillable = [
        'estado_id',
        'nombre',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // RELACIONES
    public function estado()
    {
        return $this->belongsTo(EstadoVenezuela::class, 'estado_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'ciudad_id');
    }

    public function direccionesEnvio()
    {
        return $this->hasMany(DireccionEnvio::class, 'ciudad_id');
    }

    // SCOPE
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEstado($query, $estadoId)
    {
        return $query->where('estado_id', $estadoId);
    }

    // MÉTODOS
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre}, {$this->estado->nombre}";
    }
}
