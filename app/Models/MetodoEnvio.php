<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoEnvio extends Model
{
    use HasFactory;

    protected $table = 'metodos_envio';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'costo_base',
        'tipo_costo',
        'configuracion',
        'zonas_cobertura',
        'dias_entrega_min',
        'dias_entrega_max',
        'activo',
        'envio_gratis_minimo',
        'minimo_envio_gratis',
        'empresa',
        'telefono',
        'email',
        'sitio_web',
        'seguro_disponible',
        'costo_seguro_porcentaje',
        'seguimiento_disponible',
        'requiere_dimensiones',
        'requiere_peso',
        'imagen',
        'color',
        'orden',
        'tipo_servicio',
        'horario_recoleccion',
        'horario_entrega',
        'restricciones',
    ];

    protected $casts = [
        'costo_base' => 'decimal:2',
        'activo' => 'boolean',
        'envio_gratis_minimo' => 'boolean',
        'minimo_envio_gratis' => 'decimal:2',
        'configuracion' => 'array',
        'zonas_cobertura' => 'array',
        'costo_seguro_porcentaje' => 'decimal:2',
        'seguro_disponible' => 'boolean',
        'seguimiento_disponible' => 'boolean',
        'requiere_dimensiones' => 'boolean',
        'requiere_peso' => 'boolean',
        'orden' => 'integer',
        'restricciones' => 'array',
    ];

    // MÉTODOS
    public function calcularCosto($subtotal, $datos = [])
    {
        if ($this->envio_gratis_minimo && $subtotal >= $this->minimo_envio_gratis) {
            return 0;
        }
        
        $costo = $this->costo_base;
        
        switch ($this->tipo_costo) {
            case 'por_peso':
                $peso = $datos['peso'] ?? 1;
                $costo = $costo * $peso;
                break;
                
            case 'por_distancia':
                $distancia = $datos['distancia'] ?? 0;
                $costo = $costo * $distancia;
                break;
                
            case 'por_zona':
                $zona = $datos['zona'] ?? 'default';
                $costo = $this->configuracion['zonas'][$zona] ?? $costo;
                break;
        }
        
        // Aplicar seguro si está solicitado
        if (isset($datos['seguro']) && $datos['seguro'] && $this->seguro_disponible) {
            $costoSeguro = ($subtotal * $this->costo_seguro_porcentaje) / 100;
            $costo += $costoSeguro;
        }
        
        return round($costo, 2);
    }

    public function cubreUbicacion($estado, $ciudad = null)
    {
        if (empty($this->zonas_cobertura)) {
            return true; // Cubre todo si no hay restricciones
        }
        
        foreach ($this->zonas_cobertura as $zona) {
            if ($zona['estado'] === $estado) {
                if (empty($zona['ciudades']) || in_array($ciudad, $zona['ciudades'])) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function obtenerZonaParaUbicacion($estado, $ciudad = null)
    {
        if (empty($this->zonas_cobertura)) {
            return null;
        }
        
        foreach ($this->zonas_cobertura as $zona) {
            if ($zona['estado'] === $estado) {
                if (empty($zona['ciudades']) || in_array($ciudad, $zona['ciudades'])) {
                    return $zona;
                }
            }
        }
        
        return null;
    }

    public function calcularFechaEntrega($fechaEnvio = null)
    {
        $fechaEnvio = $fechaEnvio ?? now();
        
        $fechaMinima = $fechaEnvio->copy()->addDays($this->dias_entrega_min);
        $fechaMaxima = $fechaEnvio->copy()->addDays($this->dias_entrega_max);
        
        return [
            'fecha_minima' => $fechaMinima,
            'fecha_maxima' => $fechaMaxima,
            'rango' => "{$this->dias_entrega_min} - {$this->dias_entrega_max} días hábiles",
        ];
    }

    public function obtenerRestriccionesTexto()
    {
        if (empty($this->restricciones)) {
            return 'Sin restricciones especiales';
        }
        
        $textos = [];
        
        if (isset($this->restricciones['peso_maximo'])) {
            $textos[] = "Peso máximo: {$this->restricciones['peso_maximo']} kg";
        }
        
        if (isset($this->restricciones['dimensiones_maximas'])) {
            $dim = $this->restricciones['dimensiones_maximas'];
            $textos[] = "Dimensiones máximas: {$dim['largo']}x{$dim['ancho']}x{$dim['alto']} cm";
        }
        
        if (isset($this->restricciones['productos_restringidos'])) {
            $textos[] = "Productos restringidos: " . implode(', ', $this->restricciones['productos_restringidos']);
        }
        
        if (isset($this->restricciones['horario_recoleccion'])) {
            $textos[] = "Horario recolección: {$this->restricciones['horario_recoleccion']}";
        }
        
        return implode(' | ', $textos);
    }

    public function generarCodigoSeguimiento($pedidoId)
    {
        $prefijo = strtoupper(substr($this->codigo, 0, 3));
        $fecha = date('Ymd');
        $numero = str_pad($pedidoId, 6, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5($pedidoId . time()), 0, 4));
        
        return "{$prefijo}{$fecha}{$numero}{$random}";
    }

    public function obtenerEnlaceSeguimiento($codigoSeguimiento)
    {
        if (!$this->seguimiento_disponible || empty($this->configuracion['url_seguimiento'])) {
            return null;
        }
        
        $url = $this->configuracion['url_seguimiento'];
        return str_replace('{codigo}', $codigoSeguimiento, $url);
    }

    // SCOPE
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeParaUbicacion($query, $estado, $ciudad = null)
    {
        return $query->where(function($q) use ($estado, $ciudad) {
            $q->whereNull('zonas_cobertura')
              ->orWhereJsonContains('zonas_cobertura', [['estado' => $estado]]);
        });
    }

    public function scopeConEnvioGratis($query)
    {
        return $query->where('envio_gratis_minimo', true);
    }

    public function scopeConSeguimiento($query)
    {
        return $query->where('seguimiento_disponible', true);
    }

    public function scopeRapidos($query)
    {
        return $query->where('dias_entrega_max', '<=', 3);
    }

    public function scopeEconomicos($query)
    {
        return $query->orderBy('costo_base');
    }
}