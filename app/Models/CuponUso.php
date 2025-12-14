<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuponUso extends Model
{
    use HasFactory;

    protected $table = 'cupon_usos';

    protected $fillable = [
        'cupon_id',
        'user_id',
        'pedido_id',
        'descuento_aplicado',
        'fecha_uso',
    ];

    protected $casts = [
        'descuento_aplicado' => 'decimal:2',
        'fecha_uso' => 'datetime',
    ];

    // RELACIONES
    public function cupon()
    {
        return $this->belongsTo(Cupon::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // MÉTODOS
    public static function registrarUso($cuponId, $userId, $pedidoId, $descuento)
    {
        return self::create([
            'cupon_id' => $cuponId,
            'user_id' => $userId,
            'pedido_id' => $pedidoId,
            'descuento_aplicado' => $descuento,
            'fecha_uso' => now(),
        ]);
    }

    public function getAhorroFormateadoAttribute()
    {
        return number_format($this->descuento_aplicado, 2) . ' Bs';
    }

    // SCOPE
    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDelCupon($query, $cuponId)
    {
        return $query->where('cupon_id', $cuponId);
    }

    public function scopeEnPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_uso', [$fechaInicio, $fechaFin]);
    }

    public function scopeRecientes($query, $limit = 10)
    {
        return $query->orderBy('fecha_uso', 'desc')->limit($limit);
    }
}
