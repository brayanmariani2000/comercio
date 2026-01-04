<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MunicipioVenezuela extends Model
{
    use HasFactory;

    protected $table = 'municipios_venezuela';

    protected $fillable = [
        'estado_id',
        'nombre',
        'activo',
        'codigo',
        'capital',
        'superficie',
        'poblacion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'superficie' => 'decimal:2',
        'poblacion' => 'integer',
    ];

    // RELACIONES
   public function estado()
    {
        return $this->belongsTo(EstadoVenezuela::class, 'estado_id');
        // El segundo parámetro especifica la columna FK en municipios_venezuela
    }

    public function ciudades()
    {
        return $this->hasMany(CiudadVenezuela::class, 'municipio_id');
    }

    public function direccionesEnvio()
    {
        return $this->hasMany(DireccionEnvio::class, 'ciudad_id');
    }

    // SCOPE
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEstado($query, $estadoId)
    {
        return $query->where('estado_id', $estadoId);
    }
}