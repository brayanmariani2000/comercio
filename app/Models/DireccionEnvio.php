<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DireccionEnvio extends Model
{
    use HasFactory;

    protected $table = 'direcciones_envio';

    protected $fillable = [
        'user_id',
        'alias',
        'nombre_completo',
        'cedula',
        'telefono',
        'direccion',
        'estado_id',
        'ciudad_id',
        'codigo_postal',
        'instrucciones',
        'principal',
        'activo',
        'referencia',
        'urbanizacion',
        'calle',
        'casa_apto',
        'punto_referencia',
        'horario_preferido',
        'dias_disponibles',
        'coordenadas_gps',
    ];

    protected $casts = [
        'principal' => 'boolean',
        'activo' => 'boolean',
        'dias_disponibles' => 'array',
        'coordenadas_gps' => 'array',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estado()
    {
        return $this->belongsTo(EstadoVenezuela::class, 'estado_id');
    }

    public function ciudad()
    {
        return $this->belongsTo(CiudadVenezuela::class, 'ciudad_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'direccion_envio_id');
    }

    // MÉTODOS
    public function getDireccionCompletaAttribute()
    {
        $partes = [];
        
        if ($this->urbanizacion) {
            $partes[] = $this->urbanizacion;
        }
        
        if ($this->calle) {
            $partes[] = $this->calle;
        }
        
        if ($this->casa_apto) {
            $partes[] = $this->casa_apto;
        }
        
        if ($this->direccion) {
            $partes[] = $this->direccion;
        }
        
        if ($this->ciudad && $this->ciudad->nombre) {
            $partes[] = $this->ciudad->nombre;
        }
        
        if ($this->estado && $this->estado->nombre) {
            $partes[] = $this->estado->nombre;
        }
        
        if ($this->codigo_postal) {
            $partes[] = "C.P. {$this->codigo_postal}";
        }
        
        return implode(', ', $partes);
    }

    public function marcarComoPrincipal()
    {
        // Quitar principal de otras direcciones del usuario
        $this->user->direccionesEnvio()->update(['principal' => false]);
        
        $this->principal = true;
        $this->save();
        
        return $this;
    }

    public function calcularDistancia($lat, $lng)
    {
        if (!$this->coordenadas_gps || !isset($this->coordenadas_gps['lat']) || !isset($this->coordenadas_gps['lng'])) {
            return null;
        }
        
        $lat1 = $this->coordenadas_gps['lat'];
        $lng1 = $this->coordenadas_gps['lng'];
        $lat2 = $lat;
        $lng2 = $lng;
        
        $earthRadius = 6371; // Radio de la Tierra en kilómetros
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        $distance = $earthRadius * $c;
        
        return round($distance, 2); // Distancia en kilómetros
    }

    public function esValidaParaEnvio()
    {
        if (!$this->activo) {
            return false;
        }
        
        if (!$this->nombre_completo || !$this->telefono || !$this->direccion) {
            return false;
        }
        
        if (!$this->estado_id || !$this->ciudad_id) {
            return false;
        }
        
        // Verificar si la ciudad está activa para envíos
        if (!$this->ciudad || !$this->ciudad->activo) {
            return false;
        }
        
        return true;
    }

    public function obtenerHorarioTexto()
    {
        if (!$this->horario_preferido) {
            return 'Cualquier horario';
        }
        
        $horarios = [
            'manana' => 'Mañana (8:00 AM - 12:00 PM)',
            'tarde' => 'Tarde (1:00 PM - 6:00 PM)',
            'noche' => 'Noche (6:00 PM - 9:00 PM)',
            'todo_dia' => 'Todo el día',
        ];
        
        return $horarios[$this->horario_preferido] ?? $this->horario_preferido;
    }

    public function obtenerDiasDisponiblesTexto()
    {
        if (!$this->dias_disponibles || empty($this->dias_disponibles)) {
            return 'Todos los días';
        }
        
        $dias = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];
        
        $diasSeleccionados = array_intersect_key($dias, array_flip($this->dias_disponibles));
        
        return implode(', ', $diasSeleccionados);
    }

    // SCOPE
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePrincipales($query)
    {
        return $query->where('principal', true);
    }

    public function scopePorEstado($query, $estadoId)
    {
        return $query->where('estado_id', $estadoId);
    }

    public function scopePorCiudad($query, $ciudadId)
    {
        return $query->where('ciudad_id', $ciudadId);
    }
}