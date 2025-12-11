<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendedor_id',
        'pedido_id',
        'monto_venta',
        'porcentaje_comision',
        'monto_comision',
        'monto_vendedor',
        'estado',
        'fecha_pago',
        'referencia_pago',
        'observaciones',
        'tipo_comision',
        'retencion_iva',
        'retencion_islr',
        'monto_neto',
        'periodo_comision',
    ];

    protected $casts = [
        'monto_venta' => 'decimal:2',
        'porcentaje_comision' => 'decimal:2',
        'monto_comision' => 'decimal:2',
        'monto_vendedor' => 'decimal:2',
        'retencion_iva' => 'decimal:2',
        'retencion_islr' => 'decimal:2',
        'monto_neto' => 'decimal:2',
        'fecha_pago' => 'date',
        'periodo_comision' => 'date',
    ];

    // RELACIONES
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function pago()
    {
        return $this->belongsTo(PagoVendedor::class, 'pago_vendedor_id');
    }

    // MÉTODOS
    public function calcularRetenciones()
    {
        // IVA (16% en Venezuela sobre la comisión)
        $this->retencion_iva = ($this->monto_comision * 0.16);
        
        // ISLR (varía según el monto, ejemplo: 1% para este caso)
        $this->retencion_islr = ($this->monto_comision * 0.01);
        
        $this->monto_neto = $this->monto_vendedor - $this->retencion_iva - $this->retencion_islr;
        
        return $this;
    }

    public function pagar($referenciaPago = null, $fechaPago = null)
    {
        $this->estado = 'pagada';
        $this->referencia_pago = $referenciaPago;
        $this->fecha_pago = $fechaPago ?? now();
        $this->save();
        
        // Generar notificación al vendedor
        $this->vendedor->user->generarNotificacion(
            'Comisión pagada',
            "Se ha procesado el pago de tu comisión por Bs. " . number_format($this->monto_neto, 2, ',', '.'),
            'pago',
            [
                'comision_id' => $this->id,
                'monto' => $this->monto_neto,
                'referencia' => $this->referencia_pago,
            ]
        );
        
        return $this;
    }

    public function generarRecibo()
    {
        return [
            'numero_comision' => 'COM-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'fecha_generacion' => now()->format('d/m/Y'),
            'vendedor' => [
                'nombre' => $this->vendedor->nombre_comercial,
                'rif' => $this->vendedor->rif,
                'direccion' => $this->vendedor->direccion_fiscal,
            ],
            'pedido' => [
                'numero' => $this->pedido->numero_pedido,
                'fecha' => $this->pedido->created_at->format('d/m/Y'),
                'monto_total' => $this->pedido->total,
            ],
            'detalle_comision' => [
                'monto_venta' => $this->monto_venta,
                'porcentaje_comision' => $this->porcentaje_comision,
                'monto_comision' => $this->monto_comision,
                'monto_vendedor' => $this->monto_vendedor,
            ],
            'retenciones' => [
                'iva' => $this->retencion_iva,
                'islr' => $this->retencion_islr,
                'total_retenciones' => $this->retencion_iva + $this->retencion_islr,
            ],
            'monto_neto' => $this->monto_neto,
            'estado' => $this->estado,
            'fecha_pago' => $this->fecha_pago ? $this->fecha_pago->format('d/m/Y') : 'Pendiente',
            'referencia_pago' => $this->referencia_pago,
        ];
    }

    // SCOPE
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    public function scopeConRetenciones($query)
    {
        return $query->whereNotNull('retencion_iva')->orWhereNotNull('retencion_islr');
    }
}

// Modelo PagoVendedor
class PagoVendedor extends Model
{
    use HasFactory;

    protected $table = 'pagos_vendedores';

    protected $fillable = [
        'vendedor_id',
        'numero_pago',
        'monto_total',
        'metodo_pago',
        'referencia',
        'banco',
        'estado',
        'comisiones_incluidas',
        'periodo_inicio',
        'periodo_fin',
        'observaciones',
        'fecha_procesamiento',
        'fecha_completado',
        'usuario_procesador_id',
        'comprobante',
        'notas_contabilidad',
        'tipo_cambio',
        'monto_dolares',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'comisiones_incluidas' => 'array',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'fecha_procesamiento' => 'datetime',
        'fecha_completado' => 'datetime',
        'tipo_cambio' => 'decimal:2',
        'monto_dolares' => 'decimal:2',
    ];

    // RELACIONES
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function usuarioProcesador()
    {
        return $this->belongsTo(User::class, 'usuario_procesador_id');
    }

    public function comisiones()
    {
        return $this->hasMany(Comision::class, 'pago_vendedor_id');
    }

    // MÉTODOS
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($pago) {
            if (empty($pago->numero_pago)) {
                $pago->numero_pago = 'PAGVEN-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    public function agregarComision($comisionId)
    {
        $comisiones = $this->comisiones_incluidas ?? [];
        
        if (!in_array($comisionId, $comisiones)) {
            $comisiones[] = $comisionId;
            $this->comisiones_incluidas = $comisiones;
            
            // Actualizar monto total
            $comision = Comision::find($comisionId);
            if ($comision) {
                $this->monto_total += $comision->monto_neto;
            }
            
            $this->save();
        }
        
        return $this;
    }

    public function procesar($usuarioId)
    {
        $this->estado = 'procesando';
        $this->fecha_procesamiento = now();
        $this->usuario_procesador_id = $usuarioId;
        $this->save();
        
        // Actualizar estado de las comisiones incluidas
        Comision::whereIn('id', $this->comisiones_incluidas ?? [])
            ->update([
                'estado' => 'procesando',
                'pago_vendedor_id' => $this->id,
            ]);
        
        return $this;
    }

    public function completar($referencia = null, $comprobante = null)
    {
        $this->estado = 'completado';
        $this->fecha_completado = now();
        $this->referencia = $referencia;
        $this->comprobante = $comprobante;
        $this->save();
        
        // Actualizar estado de las comisiones
        Comision::whereIn('id', $this->comisiones_incluidas ?? [])
            ->update([
                'estado' => 'pagada',
                'referencia_pago' => $referencia,
                'fecha_pago' => now(),
            ]);
        
        // Notificar al vendedor
        $this->vendedor->user->generarNotificacion(
            'Pago completado',
            "Tu pago #{$this->numero_pago} por Bs. " . number_format($this->monto_total, 2, ',', '.') . " ha sido completado",
            'pago',
            [
                'pago_id' => $this->id,
                'numero_pago' => $this->numero_pago,
                'monto' => $this->monto_total,
                'referencia' => $this->referencia,
            ]
        );
        
        return $this;
    }

    public function fallar($razon)
    {
        $this->estado = 'fallido';
        $this->observaciones = ($this->observaciones ? $this->observaciones . "\n" : '') . "Fallido: {$razon}";
        $this->save();
        
        // Revertir estado de las comisiones
        Comision::whereIn('id', $this->comisiones_incluidas ?? [])
            ->update([
                'estado' => 'pendiente',
                'pago_vendedor_id' => null,
            ]);
        
        return $this;
    }

    public function calcularMontoEnDolares($tipoCambio = null)
    {
        $tipoCambio = $tipoCambio ?? $this->tipo_cambio;
        
        if ($tipoCambio > 0) {
            $this->monto_dolares = $this->monto_total / $tipoCambio;
            $this->tipo_cambio = $tipoCambio;
            $this->save();
        }
        
        return $this->monto_dolares;
    }

    public function generarExtracto()
    {
        $comisiones = Comision::whereIn('id', $this->comisiones_incluidas ?? [])->get();
        
        $detalle = $comisiones->map(function($comision) {
            return [
                'pedido' => $comision->pedido->numero_pedido,
                'fecha_venta' => $comision->pedido->created_at->format('d/m/Y'),
                'monto_venta' => $comision->monto_venta,
                'comision' => $comision->monto_comision,
                'retenciones' => $comision->retencion_iva + $comision->retencion_islr,
                'neto' => $comision->monto_neto,
            ];
        });
        
        return [
            'numero_pago' => $this->numero_pago,
            'periodo' => $this->periodo_inicio->format('d/m/Y') . ' - ' . $this->periodo_fin->format('d/m/Y'),
            'vendedor' => [
                'nombre' => $this->vendedor->nombre_comercial,
                'rif' => $this->vendedor->rif,
                'cuenta' => $this->vendedor->cuenta_bancaria ?? 'No especificada',
            ],
            'resumen' => [
                'total_comisiones' => $comisiones->count(),
                'monto_total_ventas' => $comisiones->sum('monto_venta'),
                'monto_total_comisiones' => $comisiones->sum('monto_comision'),
                'monto_total_retenciones' => $comisiones->sum(function($c) {
                    return $c->retencion_iva + $c->retencion_islr;
                }),
                'monto_neto_total' => $this->monto_total,
            ],
            'detalle' => $detalle,
            'informacion_pago' => [
                'metodo' => $this->metodo_pago,
                'referencia' => $this->referencia,
                'banco' => $this->banco,
                'fecha_procesamiento' => $this->fecha_procesamiento?->format('d/m/Y H:i'),
                'fecha_completado' => $this->fecha_completado?->format('d/m/Y H:i'),
            ],
        ];
    }

    // SCOPE
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeProcesando($query)
    {
        return $query->where('estado', 'procesando');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopeFallidos($query)
    {
        return $query->where('estado', 'fallido');
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('periodo_inicio', [$fechaInicio, $fechaFin]);
    }

    public function scopeRecientes($query, $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}