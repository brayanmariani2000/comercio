<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoVenezuela extends Model
{
    use HasFactory;

    protected $table = 'estados_venezuela';  // ← CORREGIR: estaba 'municipios_venezuela'

    protected $fillable = [
        'nombre',
        'capital',
        'region',
        'municipios',  // ← número de municipios, no relación
        'activo',
        'codigo',
        'zona_horaria',
        'latitud',
        'longitud',
        'poblacion',
        'superficie',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'municipios' => 'integer',  // ← número, no relación
        'poblacion' => 'integer',
        'superficie' => 'decimal:2',
        'latitud' => 'decimal:6',
        'longitud' => 'decimal:6',
    ];

      // RELACIÓN CORREGIDA
    public function municipios()
    {
        return $this->hasMany(MunicipioVenezuela::class, 'estado_id');
        // El segundo parámetro especifica la columna FK en municipios_venezuela
    }

    public function ciudades()
    {
        return $this->hasManyThrough(
            CiudadVenezuela::class, 
            MunicipioVenezuela::class,
            'estado_id', // FK en municipios_venezuela
            'municipio_id', // FK en ciudades_venezuela
            'id', // PK en estados_venezuela
            'id' // PK en municipios_venezuela
        );
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function direccionesEnvio()
    {
        return $this->hasMany(DireccionEnvio::class);
    }

    // ELIMINAR estos métodos que no corresponden:
    // public function estado() { ... } ← NO pertenece aquí
    // public function ciudades() con return $this->hasMany(...) ← NO, usar hasManyThrough
}