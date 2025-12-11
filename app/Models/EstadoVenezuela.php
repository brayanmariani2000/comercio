<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoVenezuela extends Model
{
    use HasFactory;

    protected $table = 'estados_venezuela';

    protected $fillable = [
        'nombre',
        'capital',
        'region',
        'municipios',
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
        'municipios' => 'integer',
        'poblacion' => 'integer',
        'superficie' => 'decimal:2',
        'latitud' => 'decimal:6',
        'longitud' => 'decimal:6',
    ];

    // RELACIONES
    public function municipios()
    {
        return $this->hasMany(MunicipioVenezuela::class);
    }

    public function ciudades()
    {
        return $this->hasManyThrough(CiudadVenezuela::class, MunicipioVenezuela::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function vendedores()
    {
        return $this->hasMany(Vendedor::class, 'estado', 'nombre');
    }

    public function direccionesEnvio()
    {
        return $this->hasMany(DireccionEnvio::class);
    }

    // MÉTODOS
    public function obtenerEstadisticas()
    {
        $totalUsuarios = $this->usuarios()->count();
        $totalVendedores = $this->vendedores()->count();
        $totalDirecciones = $this->direccionesEnvio()->count();
        
        return [
            'total_usuarios' => $totalUsuarios,
            'total_vendedores' => $totalVendedores,
            'total_direcciones' => $totalDirecciones,
            'municipios_activos' => $this->municipios()->where('activo', true)->count(),
            'ciudades_activas' => $this->ciudades()->where('activo', true)->count(),
            'densidad_poblacional' => $this->poblacion && $this->superficie > 0 
                ? round($this->poblacion / $this->superficie, 2) 
                : 0,
        ];
    }

    public function obtenerZonasCercanas($radioKm = 50)
    {
        return self::where('id', '!=', $this->id)
            ->where('activo', true)
            ->get()
            ->filter(function($estado) use ($radioKm) {
                $distancia = $this->calcularDistancia($estado->latitud, $estado->longitud);
                return $distancia <= $radioKm;
            })
            ->sortBy(function($estado) {
                return $this->calcularDistancia($estado->latitud, $estado->longitud);
            });
    }

    public function calcularDistancia($lat2, $lng2)
    {
        if (!$this->latitud || !$this->longitud || !$lat2 || !$lng2) {
            return null;
        }
        
        $earthRadius = 6371;
        
        $dLat = deg2rad($lat2 - $this->latitud);
        $dLng = deg2rad($lng2 - $this->longitud);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($this->latitud)) * cos(deg2rad($lat2)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return round($earthRadius * $c, 2);
    }

    // SCOPE
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeConMasUsuarios($query, $limit = 10)
    {
        return $query->withCount('usuarios')->orderBy('usuarios_count', 'desc')->limit($limit);
    }

    public function scopeConMasVendedores($query, $limit = 10)
    {
        return $query->withCount('vendedores')->orderBy('vendedores_count', 'desc')->limit($limit);
    }
}

// Modelo MunicipioVenezuela
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
        return $this->belongsTo(EstadoVenezuela::class);
    }

    public function ciudades()
    {
        return $this->hasMany(CiudadVenezuela::class);
    }

    public function direccionesEnvio()
    {
        return $this->hasMany(DireccionEnvio::class, 'ciudad_id');
    }
}

// Modelo CiudadVenezuela
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

    // MÉTODOS
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre}, {$this->municipio->nombre}, {$this->estado->nombre}";
    }
}