<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiudadVenezuela extends Model
{
    use HasFactory;

    protected $table = 'ciudades_venezuela';

    protected $fillable = [
        'municipio_id',
        'nombre',
        'codigo_postal',
        'activo',
        'latitud',
        'longitud',
        'poblacion',
        'altitud',
        'clima',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'latitud' => 'decimal:6',
        'longitud' => 'decimal:6',
        'poblacion' => 'integer',
        'altitud' => 'integer',
    ];

    // RELACIONES
    public function municipio()
    {
        return $this->belongsTo(MunicipioVenezuela::class);
    }

    public function estado()
    {
        return $this->belongsToThrough(EstadoVenezuela::class, MunicipioVenezuela::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function direccionesEnvio()
    {
        return $this->hasMany(DireccionEnvio::class);
    }

    // SCOPE
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorMunicipio($query, $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    // MÉTODOS
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre}, {$this->municipio->nombre}, {$this->estado->nombre}";
    }
}