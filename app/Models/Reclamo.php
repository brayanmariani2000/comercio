<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reclamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_reclamo',
        'pedido_id',
        'user_id',
        'tipo_reclamo',
        'descripcion',
        'evidencias',
        'estado',
        'solucion',
        'fecha_resolucion',
        'categoria_reclamo',
        'prioridad',
        'asignado_a',
        'fecha_limite_respuesta',
        'resolucion_aceptada',
        'comentario_resolucion',
        'reembolso_solicitado',
        'monto_reembolso',
        'producto_reemplazo_id',
        'tiempo_respuesta',
    ];

    protected $casts = [
        'evidencias' => 'array',
        'fecha_resolucion' => 'datetime',
        'fecha_limite_respuesta' => 'datetime',
        'resolucion_aceptada' => 'boolean',
        'reembolso_solicitado' => 'boolean',
        'monto_reembolso' => 'decimal:2',
        'prioridad' => 'integer',
        'tiempo_respuesta' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($reclamo) {
            if (empty($reclamo->codigo_reclamo)) {
                $reclamo->codigo_reclamo = 'REC-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
            
            if (empty($reclamo->prioridad)) {
                $reclamo->prioridad = $reclamo->calcularPrioridad();
            }
            
            $reclamo->fecha_limite_respuesta = now()->addDays(3);
        });
        
        static::created(function ($reclamo) {
            $reclamo->generarNotificaciones();
        });
        
        static::updated(function ($reclamo) {
            if ($reclamo->isDirty('estado')) {
                $reclamo->generarNotificacionEstado();
            }
            
            if ($reclamo->isDirty('asignado_a')) {
                $reclamo->generarNotificacionAsignacion();
            }
        });
    }

    // RELACIONES
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asignadoA()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function productoReemplazo()
    {
        return $this->belongsTo(Producto::class, 'producto_reemplazo_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(ReclamoSeguimiento::class);
    }

    // MÉTODOS
    public function calcularPrioridad()
    {
        $prioridad = 3; // Normal
        
        $tipoPrioridad = [
            'producto_defectuoso' => 1,
            'no_recibido' => 1,
            'producto_incorrecto' => 2,
            'tardio' => 2,
            'garantia' => 2,
            'otro' => 3,
        ];
        
        if (isset($tipoPrioridad[$this->tipo_reclamo])) {
            $prioridad = $tipoPrioridad[$this->tipo_reclamo];
        }
        
        // Aumentar prioridad si es cliente frecuente
        if ($this->user && $this->user->total_compras > 10) {
            $prioridad = max(1, $prioridad - 1);
        }
        
        return $prioridad;
    }

    public function generarNotificaciones()
    {
        // Notificar al usuario
        $this->user->generarNotificacion(
            'Reclamo registrado',
            "Tu reclamo {$this->codigo_reclamo} ha sido registrado exitosamente",
            'reclamo',
            ['reclamo_id' => $this->id, 'codigo_reclamo' => $this->codigo_reclamo]
        );
        
        // Notificar al vendedor
        $this->pedido->vendedor->user->generarNotificacion(
            'Nuevo reclamo recibido',
            "Se ha registrado un reclamo para el pedido {$this->pedido->numero_pedido}",
            'reclamo',
            ['reclamo_id' => $this->id, 'pedido_id' => $this->pedido_id]
        );
        
        // Notificar a administradores/supervisores
        $administradores = User::whereIn('tipo_usuario', ['administrador', 'supervisor'])
            ->where('activo', true)
            ->get();
            
        foreach ($administradores as $admin) {
            $admin->generarNotificacion(
                'Nuevo reclamo registrado',
                "Se ha registrado un nuevo reclamo: {$this->codigo_reclamo}",
                'reclamo',
                ['reclamo_id' => $this->id, 'prioridad' => $this->prioridad]
            );
        }
    }

    public function generarNotificacionEstado()
    {
        $estados = [
            'en_revision' => 'está en revisión',
            'resuelto' => 'ha sido resuelto',
            'cerrado' => 'ha sido cerrado',
        ];
        
        if (isset($estados[$this->estado])) {
            $this->user->generarNotificacion(
                "Reclamo {$this->estado}",
                "Tu reclamo {$this->codigo_reclamo} {$estados[$this->estado]}",
                'reclamo',
                ['reclamo_id' => $this->id, 'estado' => $this->estado]
            );
        }
    }

    public function generarNotificacionAsignacion()
    {
        if ($this->asignadoA) {
            $this->asignadoA->generarNotificacion(
                'Reclamo asignado',
                "Se te ha asignado el reclamo {$this->codigo_reclamo}",
                'reclamo',
                ['reclamo_id' => $this->id, 'prioridad' => $this->prioridad]
            );
        }
    }

    public function agregarSeguimiento($descripcion, $usuarioId = null, $tipo = 'comentario')
    {
        return $this->seguimientos()->create([
            'user_id' => $usuarioId ?? auth()->id(),
            'descripcion' => $descripcion,
            'tipo' => $tipo,
        ]);
    }

    public function procesarResolucion($solucion, $usuarioId = null)
    {
        $this->estado = 'resuelto';
        $this->solucion = $solucion;
        $this->fecha_resolucion = now();
        $this->tiempo_respuesta = $this->created_at->diffInHours(now());
        $this->save();
        
        $this->agregarSeguimiento(
            "Resolución aplicada: {$solucion}",
            $usuarioId,
            'resolucion'
        );
        
        // Si hay reembolso solicitado, procesarlo
        if ($this->reembolso_solicitado && $this->monto_reembolso > 0) {
            $this->procesarReembolso();
        }
        
        // Si hay producto de reemplazo, gestionarlo
        if ($this->producto_reemplazo_id) {
            $this->procesarReemplazo();
        }
        
        return $this;
    }

    public function procesarReembolso()
    {
        // Aquí implementar la lógica de reembolso
        // Dependerá del método de pago original
        $this->agregarSeguimiento(
            "Reembolso procesado por Bs. " . number_format($this->monto_reembolso, 2, ',', '.'),
            null,
            'reembolso'
        );
        
        // Registrar en bitácora
        BitacoraSistema::registrar(
            auth()->id() ?? $this->asignado_a,
            'procesar_reembolso',
            'Reclamo',
            $this->id,
            "Reembolso procesado por Bs. {$this->monto_reembolso}",
            [],
            ['monto_reembolso' => $this->monto_reembolso]
        );
    }

    public function procesarReemplazo()
    {
        // Crear un nuevo pedido para el reemplazo
        $nuevoPedido = $this->pedido->replicate();
        $nuevoPedido->numero_pedido = Pedido::generarNumeroPedido();
        $nuevoPedido->codigo_qr = Pedido::generarCodigoQR();
        $nuevoPedido->serial_compra = Pedido::generarSerialCompra();
        $nuevoPedido->estado_pedido = 'confirmado';
        $nuevoPedido->notas = "Reemplazo por reclamo {$this->codigo_reclamo}";
        $nuevoPedido->save();
        
        // Agregar el producto de reemplazo
        $nuevoPedido->items()->create([
            'producto_id' => $this->producto_reemplazo_id,
            'cantidad' => 1,
            'precio_unitario' => 0, // Gratis como reemplazo
            'subtotal' => 0,
        ]);
        
        $this->agregarSeguimiento(
            "Producto de reemplazo asignado: Pedido {$nuevoPedido->numero_pedido}",
            null,
            'reemplazo'
        );
    }

    public function aceptarResolucion($comentario = null)
    {
        $this->resolucion_aceptada = true;
        $this->comentario_resolucion = $comentario;
        $this->estado = 'cerrado';
        $this->save();
        
        $this->agregarSeguimiento(
            "Resolución aceptada por el cliente" . ($comentario ? ": {$comentario}" : ""),
            $this->user_id,
            'aceptacion'
        );
        
        return $this;
    }

    public function rechazarResolucion($comentario)
    {
        $this->resolucion_aceptada = false;
        $this->comentario_resolucion = $comentario;
        $this->estado = 'en_revision';
        $this->save();
        
        $this->agregarSeguimiento(
            "Resolución rechazada por el cliente: {$comentario}",
            $this->user_id,
            'rechazo'
        );
        
        return $this;
    }

    public function obtenerEstadisticas()
    {
        return [
            'tiempo_respuesta' => $this->tiempo_respuesta,
            'dias_abierto' => $this->created_at->diffInDays(now()),
            'seguimientos' => $this->seguimientos()->count(),
            'ultimo_seguimiento' => $this->seguimientos()->latest()->first(),
        ];
    }

    // SCOPE
    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'abierto');
    }

    public function scopeEnRevision($query)
    {
        return $query->where('estado', 'en_revision');
    }

    public function scopeResueltos($query)
    {
        return $query->where('estado', 'resuelto');
    }

    public function scopeCerrados($query)
    {
        return $query->where('estado', 'cerrado');
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_reclamo', $tipo);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->whereHas('pedido', function($q) use ($vendedorId) {
            $q->where('vendedor_id', $vendedorId);
        });
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_limite_respuesta', '<', now())
            ->whereIn('estado', ['abierto', 'en_revision']);
    }

    public function scopeAsignadosA($query, $userId)
    {
        return $query->where('asignado_a', $userId);
    }

    public function scopeSinAsignar($query)
    {
        return $query->whereNull('asignado_a');
    }
}