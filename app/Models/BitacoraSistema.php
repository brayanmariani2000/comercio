<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraSistema extends Model
{
    use HasFactory;

    protected $table = 'bitacora_sistema';

    protected $fillable = [
        'user_id',
        'accion',
        'modelo',
        'modelo_id',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'ip_address',
        'user_agent',
        'url',
        'metodo_http',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // MÉTODOS ESTÁTICOS
    public static function registrar($userId, $accion, $modelo = null, $modeloId = null, 
                                     $descripcion = '', $datosAnteriores = null, 
                                     $datosNuevos = null)
    {
        return self::create([
            'user_id' => $userId,
            'accion' => $accion,
            'modelo' => $modelo,
            'modelo_id' => $modeloId,
            'descripcion' => $descripcion,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'metodo_http' => request()->method(),
        ]);
    }

    public static function registrarLogin($userId, $exitoso = true, $mensaje = '')
    {
        return self::registrar(
            $userId,
            $exitoso ? 'login_exitoso' : 'login_fallido',
            'User',
            $userId,
            $mensaje ?: ($exitoso ? 'Inicio de sesión exitoso' : 'Intento de inicio de sesión fallido'),
            null,
            ['exitoso' => $exitoso, 'ip' => request()->ip()]
        );
    }

    public static function registrarLogout($userId)
    {
        return self::registrar(
            $userId,
            'logout',
            'User',
            $userId,
            'Cierre de sesión',
            null,
            ['ip' => request()->ip()]
        );
    }

    public static function registrarCreacion($userId, $modelo, $modeloId, $datos)
    {
        return self::registrar(
            $userId,
            'crear',
            $modelo,
            $modeloId,
            "Creación de {$modelo}",
            null,
            $datos
        );
    }

    public static function registrarActualizacion($userId, $modelo, $modeloId, $datosAnteriores, $datosNuevos)
    {
        return self::registrar(
            $userId,
            'actualizar',
            $modelo,
            $modeloId,
            "Actualización de {$modelo}",
            $datosAnteriores,
            $datosNuevos
        );
    }

    public static function registrarEliminacion($userId, $modelo, $modeloId, $datos)
    {
        return self::registrar(
            $userId,
            'eliminar',
            $modelo,
            $modeloId,
            "Eliminación de {$modelo}",
            $datos,
            null
        );
    }

    // MÉTODOS DE INSTANCIA
    public function obtenerCambiosFormateados()
    {
        $cambios = [];
        
        if ($this->datos_anteriores && $this->datos_nuevos) {
            foreach ($this->datos_nuevos as $key => $nuevoValor) {
                $anteriorValor = $this->datos_anteriores[$key] ?? null;
                
                if ($anteriorValor != $nuevoValor) {
                    $cambios[$key] = [
                        'anterior' => $anteriorValor,
                        'nuevo' => $nuevoValor,
                    ];
                }
            }
        }
        
        return $cambios;
    }

    public function esCritica()
    {
        $accionesCriticas = [
            'eliminar',
            'login_fallido',
            'suspender_usuario',
            'bloquear_vendedor',
            'reembolso',
            'cambio_precio_masivo',
        ];
        
        return in_array($this->accion, $accionesCriticas);
    }

    // SCOPE
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorModelo($query, $modelo)
    {
        return $query->where('modelo', $modelo);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    public function scopeCriticas($query)
    {
        $accionesCriticas = [
            'eliminar',
            'login_fallido',
            'suspender_usuario',
            'bloquear_vendedor',
            'reembolso',
            'cambio_precio_masivo',
        ];
        
        return $query->whereIn('accion', $accionesCriticas);
    }

    public function scopeRecientes($query, $limit = 100)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeConIP($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    // Método para obtener estadísticas
    public static function obtenerEstadisticas($fechaInicio = null, $fechaFin = null)
    {
        $fechaInicio = $fechaInicio ?? now()->subMonth();
        $fechaFin = $fechaFin ?? now();
        
        $query = self::whereBetween('created_at', [$fechaInicio, $fechaFin]);
        
        return [
            'total_registros' => $query->count(),
            'por_accion' => $query->selectRaw('accion, COUNT(*) as total')
                ->groupBy('accion')
                ->orderBy('total', 'desc')
                ->get(),
            'por_usuario' => $query->selectRaw('user_id, COUNT(*) as total')
                ->groupBy('user_id')
                ->orderBy('total', 'desc')
                ->with('user')
                ->limit(10)
                ->get(),
            'por_modelo' => $query->selectRaw('modelo, COUNT(*) as total')
                ->groupBy('modelo')
                ->orderBy('total', 'desc')
                ->get(),
            'por_dia' => $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get(),
            'ip_sospechosas' => $query->selectRaw('ip_address, COUNT(*) as intentos')
                ->whereIn('accion', ['login_fallido', 'acceso_no_autorizado'])
                ->groupBy('ip_address')
                ->having('intentos', '>', 5)
                ->orderBy('intentos', 'desc')
                ->get(),
        ];
    }
}