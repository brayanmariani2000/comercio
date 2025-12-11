<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_pedido',
        'codigo_qr',
        'serial_compra',
        'user_id',
        'vendedor_id',
        'nombre_cliente',
        'cedula_cliente',
        'telefono_cliente',
        'email_cliente',
        'direccion_envio',
        'ciudad_envio',
        'estado_envio',
        'subtotal',
        'envio',
        'iva',
        'total',
        'metodo_pago',
        'referencia_pago',
        'banco',
        'estado_pago',
        'estado_pedido',
        'notas',
        'comentario_reclamo',
        'metodo_envio_id',
        'costo_envio_calculado',
        'seguro_envio',
        'fecha_estimada_entrega',
        'codigo_seguimiento',
        'direccion_facturacion',
        'rif_facturacion',
        'razon_social_facturacion',
        'direccion_alternativa',
        'telefono_alternativo',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'envio' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
        'costo_envio_calculado' => 'decimal:2',
        'seguro_envio' => 'boolean',
        'fecha_pago' => 'datetime',
        'fecha_envio' => 'datetime',
        'fecha_entrega' => 'datetime',
        'fecha_estimada_entrega' => 'date',
        'ultima_validacion' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($pedido) {
            if (empty($pedido->numero_pedido)) {
                $pedido->numero_pedido = self::generarNumeroPedido();
            }
            
            if (empty($pedido->codigo_qr)) {
                $pedido->codigo_qr = self::generarCodigoQR();
            }
            
            if (empty($pedido->serial_compra)) {
                $pedido->serial_compra = self::generarSerialCompra();
            }
        });
        
        static::created(function ($pedido) {
            // Generar notificaciones
            $pedido->generarNotificacionCreacion();
        });
        
        static::updated(function ($pedido) {
            if ($pedido->isDirty('estado_pedido')) {
                $pedido->generarNotificacionEstado();
            }
            
            if ($pedido->isDirty('estado_pago')) {
                $pedido->generarNotificacionPago();
            }
        });
    }

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function metodoEnvio()
    {
        return $this->belongsTo(MetodoEnvio::class, 'metodo_envio_id');
    }

    public function cupon()
    {
        return $this->hasOne(CuponUso::class);
    }

    public function reclamos()
    {
        return $this->hasMany(Reclamo::class);
    }

    public function validaciones()
    {
        return $this->hasMany(Validacion::class);
    }

    public function comision()
    {
        return $this->hasOne(Comision::class);
    }

    public function conversacion()
    {
        return $this->hasOne(Conversacion::class);
    }

    // MÉTODOS ESTÁTICOS
    public static function generarNumeroPedido()
    {
        $prefix = 'PED-';
        $date = date('Ymd');
        $lastPedido = self::where('numero_pedido', 'like', "{$prefix}{$date}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPedido) {
            $lastNumber = intval(substr($lastPedido->numero_pedido, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "{$prefix}{$date}-{$newNumber}";
    }

    public static function generarCodigoQR()
    {
        do {
            $codigo = 'QR' . Str::upper(Str::random(10)) . time();
        } while (self::where('codigo_qr', $codigo)->exists());
        
        return $codigo;
    }

    public static function generarSerialCompra()
    {
        do {
            $serial = 'VEN' . date('ymd') . Str::upper(Str::random(6));
        } while (self::where('serial_compra', $serial)->exists());
        
        return $serial;
    }

    public static function crearDesdeCarrito(User $user, Carrito $carrito, $datos)
    {
        \DB::beginTransaction();
        
        try {
            // Calcular subtotal
            $subtotal = $carrito->items->sum(function($item) {
                return $item->cantidad * $item->precio_unitario;
            });
            
            // Aplicar cupón si existe
            $descuentoCupon = 0;
            if (isset($datos['cupon_codigo'])) {
                $cupon = Cupon::validar($datos['cupon_codigo'], $user->id, $subtotal);
                if ($cupon) {
                    $descuentoCupon = $cupon->calcularDescuento($subtotal);
                    $subtotal -= $descuentoCupon;
                }
            }
            
            // Calcular IVA (16% en Venezuela)
            $iva = $subtotal * 0.16;
            
            // Calcular envío
            $envio = 0;
            if (isset($datos['metodo_envio_id'])) {
                $metodoEnvio = MetodoEnvio::find($datos['metodo_envio_id']);
                if ($metodoEnvio) {
                    $envio = $metodoEnvio->calcularCosto($subtotal, $datos);
                }
            }
            
            $total = $subtotal + $iva + $envio;
            
            // Crear pedido
            $pedido = self::create([
                'user_id' => $user->id,
                'vendedor_id' => $carrito->items->first()->producto->vendedor_id,
                'nombre_cliente' => $datos['nombre_cliente'] ?? $user->name,
                'cedula_cliente' => $datos['cedula_cliente'] ?? $user->cedula,
                'telefono_cliente' => $datos['telefono_cliente'] ?? $user->telefono,
                'email_cliente' => $datos['email_cliente'] ?? $user->email,
                'direccion_envio' => $datos['direccion_envio'],
                'ciudad_envio' => $datos['ciudad_envio'],
                'estado_envio' => $datos['estado_envio'],
                'subtotal' => $subtotal,
                'envio' => $envio,
                'iva' => $iva,
                'total' => $total,
                'metodo_pago' => $datos['metodo_pago'],
                'metodo_envio_id' => $datos['metodo_envio_id'] ?? null,
                'costo_envio_calculado' => $envio,
                'seguro_envio' => $datos['seguro_envio'] ?? false,
                'direccion_facturacion' => $datos['direccion_facturacion'] ?? null,
                'rif_facturacion' => $datos['rif_facturacion'] ?? null,
                'razon_social_facturacion' => $datos['razon_social_facturacion'] ?? null,
                'notas' => $datos['notas'] ?? null,
            ]);
            
            // Crear items del pedido
            foreach ($carrito->items as $carritoItem) {
                $pedido->items()->create([
                    'producto_id' => $carritoItem->producto_id,
                    'cantidad' => $carritoItem->cantidad,
                    'precio_unitario' => $carritoItem->precio_unitario,
                    'subtotal' => $carritoItem->cantidad * $carritoItem->precio_unitario,
                ]);
                
                // Actualizar stock del producto
                $carritoItem->producto->registrarVenta($carritoItem->cantidad);
            }
            
            // Registrar cupón usado
            if (isset($cupon)) {
                CuponUso::create([
                    'cupon_id' => $cupon->id,
                    'user_id' => $user->id,
                    'pedido_id' => $pedido->id,
                    'descuento_aplicado' => $descuentoCupon,
                ]);
            }
            
            // Vaciar carrito
            $carrito->items()->delete();
            
            // Crear conversación para este pedido
            $pedido->conversacion()->create([
                'user_id' => $user->id,
                'vendedor_id' => $pedido->vendedor_id,
                'pedido_id' => $pedido->id,
                'asunto' => "Pedido {$pedido->numero_pedido}",
            ]);
            
            \DB::commit();
            
            return $pedido;
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    // MÉTODOS DE INSTANCIA
    public function generarQRImagen($size = 300)
    {
        $datos = [
            'pedido_id' => $this->id,
            'numero_pedido' => $this->numero_pedido,
            'codigo_qr' => $this->codigo_qr,
            'serial_compra' => $this->serial_compra,
            'fecha' => $this->created_at->format('Y-m-d H:i:s'),
            'total' => $this->total,
            'vendedor' => $this->vendedor->nombre_comercial,
            'cliente' => $this->nombre_cliente,
        ];

        $qrData = json_encode($datos);
        
        $qrImage = QrCode::format('png')
            ->size($size)
            ->errorCorrection('H')
            ->generate($qrData);
        
        return 'data:image/png;base64,' . base64_encode($qrImage);
    }

    public function generarCodigoBarras()
    {
        // Generar código de barras para el serial
        $barcode = \DNS1D::getBarcodePNG($this->serial_compra, 'C128');
        return 'data:image/png;base64,' . $barcode;
    }

    public function validarCompra($codigo = null, $serial = null)
    {
        if ($codigo && $this->codigo_qr === $codigo) {
            $tipo = 'qr';
            $valido = true;
        } elseif ($serial && $this->serial_compra === $serial) {
            $tipo = 'serial';
            $valido = true;
        } else {
            $tipo = 'invalido';
            $valido = false;
        }
        
        // Registrar validación
        $validacion = $this->validaciones()->create([
            'codigo_qr' => $codigo,
            'serial_compra' => $serial,
            'tipo_validacion' => $tipo,
            'dispositivo' => request()->userAgent(),
            'ubicacion' => request()->ip(),
            'resultado' => $valido ? 'Válido' : 'Inválido',
            'valido' => $valido,
        ]);
        
        $this->increment('intentos_validacion');
        $this->ultima_validacion = now();
        $this->save();
        
        return [
            'valido' => $valido,
            'tipo' => $tipo,
            'validacion_id' => $validacion->id,
            'pedido' => $valido ? $this->toArray() : null,
            'mensaje' => $valido ? 'Compra validada correctamente' : 'Código inválido'
        ];
    }

    public function actualizarEstado($nuevoEstado, $comentario = null)
    {
        $estadoAnterior = $this->estado_pedido;
        $this->estado_pedido = $nuevoEstado;
        
        // Actualizar fechas según estado
        switch ($nuevoEstado) {
            case 'confirmado':
                $this->estado_pago = 'confirmado';
                break;
            case 'enviado':
                $this->fecha_envio = now();
                break;
            case 'entregado':
                $this->fecha_entrega = now();
                // Crear comisión para el vendedor
                $this->crearComision();
                break;
            case 'cancelado':
                // Revertir stock
                $this->revertirStock();
                break;
        }
        
        $this->save();
        
        // Registrar en bitácora
        BitacoraSistema::registrar(
            auth()->id() ?? $this->user_id,
            'actualizar_estado_pedido',
            'Pedido',
            $this->id,
            "Cambio de estado: {$estadoAnterior} -> {$nuevoEstado}",
            ['estado_anterior' => $estadoAnterior],
            ['estado_nuevo' => $nuevoEstado, 'comentario' => $comentario]
        );
        
        return $this;
    }

    public function crearComision()
    {
        if ($this->comision()->exists()) {
            return;
        }
        
        $calculoComision = $this->vendedor->calcularComisionPedido($this);
        
        return $this->comision()->create([
            'vendedor_id' => $this->vendedor_id,
            'monto_venta' => $this->total,
            'porcentaje_comision' => $calculoComision['porcentaje'],
            'monto_comision' => $calculoComision['monto'],
            'monto_vendedor' => $calculoComision['monto_vendedor'],
            'estado' => 'calculada',
        ]);
    }

    public function revertirStock()
    {
        foreach ($this->items as $item) {
            $item->producto->increment('stock', $item->cantidad);
            $item->producto->decrement('ventas', $item->cantidad);
        }
    }

    public function generarNotificacionCreacion()
    {
        // Notificar al comprador
        $this->user->generarNotificacion(
            'Pedido creado',
            "Tu pedido {$this->numero_pedido} ha sido creado exitosamente",
            'pedido',
            ['pedido_id' => $this->id, 'numero_pedido' => $this->numero_pedido]
        );
        
        // Notificar al vendedor
        $this->vendedor->user->generarNotificacion(
            'Nuevo pedido recibido',
            "Has recibido un nuevo pedido: {$this->numero_pedido}",
            'pedido',
            ['pedido_id' => $this->id, 'numero_pedido' => $this->numero_pedido]
        );
    }

    public function generarNotificacionEstado()
    {
        $mensajes = [
            'confirmado' => 'ha sido confirmado',
            'preparando' => 'está siendo preparado',
            'enviado' => 'ha sido enviado',
            'entregado' => 'ha sido entregado',
            'cancelado' => 'ha sido cancelado',
        ];
        
        if (isset($mensajes[$this->estado_pedido])) {
            $this->user->generarNotificacion(
                "Pedido {$this->estado_pedido}",
                "Tu pedido {$this->numero_pedido} {$mensajes[$this->estado_pedido]}",
                'pedido',
                ['pedido_id' => $this->id, 'estado' => $this->estado_pedido]
            );
        }
    }

    public function generarNotificacionPago()
    {
        if ($this->estado_pago === 'confirmado') {
            $this->user->generarNotificacion(
                'Pago confirmado',
                "El pago de tu pedido {$this->numero_pedido} ha sido confirmado",
                'pago',
                ['pedido_id' => $this->id]
            );
            
            $this->vendedor->user->generarNotificacion(
                'Pago confirmado',
                "El pago del pedido {$this->numero_pedido} ha sido confirmado",
                'pago',
                ['pedido_id' => $this->id]
            );
        }
    }

    public function datosParaImpresion()
    {
        return [
            'numero_pedido' => $this->numero_pedido,
            'fecha' => $this->created_at->format('d/m/Y H:i'),
            'codigo_qr' => $this->codigo_qr,
            'serial_compra' => $this->serial_compra,
            'cliente' => [
                'nombre' => $this->nombre_cliente,
                'cedula' => $this->cedula_cliente,
                'telefono' => $this->telefono_cliente,
                'email' => $this->email_cliente,
                'direccion' => $this->direccion_envio,
                'ciudad' => $this->ciudad_envio,
                'estado' => $this->estado_envio,
            ],
            'vendedor' => [
                'nombre' => $this->vendedor->nombre_comercial,
                'rif' => $this->vendedor->rif,
                'direccion' => $this->vendedor->direccion_fiscal,
                'telefono' => $this->vendedor->telefono,
            ],
            'items' => $this->items->map(function($item) {
                return [
                    'producto' => $item->producto->nombre,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => number_format($item->precio_unitario, 2, ',', '.'),
                    'subtotal' => number_format($item->subtotal, 2, ',', '.'),
                ];
            }),
            'totales' => [
                'subtotal' => number_format($this->subtotal, 2, ',', '.'),
                'envio' => number_format($this->envio, 2, ',', '.'),
                'iva' => number_format($this->iva, 2, ',', '.'),
                'total' => number_format($this->total, 2, ',', '.'),
            ],
            'metodo_pago' => $this->metodo_pago,
            'referencia_pago' => $this->referencia_pago,
            'banco' => $this->banco,
            'notas' => $this->notas,
            'qr_base64' => $this->generarQRImagen(200),
            'barcode_base64' => $this->generarCodigoBarras(),
        ];
    }

    public function generarFactura()
    {
        // Generar datos para factura electrónica (Venezuela)
        return [
            'control' => 'FACT-' . $this->numero_pedido,
            'fecha' => $this->created_at->format('d/m/Y'),
            'cliente' => [
                'nombre' => $this->nombre_cliente,
                'cedula_rif' => $this->cedula_cliente,
                'direccion' => $this->direccion_envio,
                'telefono' => $this->telefono_cliente,
            ],
            'vendedor' => [
                'razon_social' => $this->vendedor->razon_social,
                'rif' => $this->vendedor->rif,
                'direccion' => $this->vendedor->direccion_fiscal,
            ],
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'iva' => $this->iva,
            'total' => $this->total,
            'forma_pago' => $this->metodo_pago,
            'observaciones' => $this->notas,
        ];
    }

    // SCOPE
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado_pedido', $estado);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    public function scopeConPagoConfirmado($query)
    {
        return $query->where('estado_pago', 'confirmado');
    }

    public function scopeRecientes($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}