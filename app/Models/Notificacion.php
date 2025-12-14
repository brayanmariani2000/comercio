<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'titulo',
        'mensaje',
        'tipo',
        'data',
        'leida',
        'leida_at',
        'canal',
        'prioridad',
        'programada_para',
        'enviada',
    ];

    protected $casts = [
        'data' => 'array',
        'leida' => 'boolean',
        'leida_at' => 'datetime',
        'programada_para' => 'datetime',
        'enviada' => 'boolean',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // MÉTODOS
    public function marcarComoLeida()
    {
        if (!$this->leida) {
            $this->leida = true;
            $this->leida_at = now();
            $this->save();
        }
        
        return $this;
    }

    public function marcarComoNoLeida()
    {
        $this->leida = false;
        $this->leida_at = null;
        $this->save();
        
        return $this;
    }

    public function enviar()
    {
        // Aquí implementar lógica de envío real (email, push, SMS)
        // Por ahora solo marcamos como enviada
        $this->enviada = true;
        $this->save();
        
        return $this;
    }

    public function obtenerEnlace()
    {
        if (!$this->data) {
            return null;
        }
        
        $data = $this->data;
        
        switch ($this->tipo) {
            case 'pedido':
                return route('pedidos.show', $data['pedido_id'] ?? '');
            case 'producto':
                return route('productos.show', $data['producto_id'] ?? '');
            case 'reclamo':
                return route('reclamos.show', $data['reclamo_id'] ?? '');
            case 'pago':
                return route('pagos.show', $data['pago_id'] ?? '');
            case 'vendedor':
                return route('vendedores.show', $data['vendedor_id'] ?? '');
            default:
                return null;
        }
    }

    public function obtenerIcono()
    {
        $iconos = [
            'pedido' => 'fas fa-shopping-bag',
            'pago' => 'fas fa-credit-card',
            'envio' => 'fas fa-truck',
            'reclamo' => 'fas fa-exclamation-triangle',
            'promocion' => 'fas fa-gift',
            'producto' => 'fas fa-box',
            'sistema' => 'fas fa-cog',
            'seguridad' => 'fas fa-shield-alt',
            'vendedor' => 'fas fa-store',
            'usuario' => 'fas fa-user',
        ];
        
        return $iconos[$this->tipo] ?? 'fas fa-bell';
    }

    public function obtenerColor()
    {
        $colores = [
            'pedido' => 'primary',
            'pago' => 'success',
            'envio' => 'info',
            'reclamo' => 'danger',
            'promocion' => 'warning',
            'producto' => 'secondary',
            'sistema' => 'dark',
            'seguridad' => 'danger',
            'vendedor' => 'primary',
            'usuario' => 'info',
        ];
        
        return $colores[$this->tipo] ?? 'secondary';
    }

    public function esImportante()
    {
        $tiposImportantes = ['reclamo', 'seguridad', 'pago'];
        return in_array($this->tipo, $tiposImportantes) || $this->prioridad === 1;
    }

    // SCOPE
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopeLeidas($query)
    {
        return $query->where('leida', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeImportantes($query)
    {
        return $query->where('prioridad', 1)
            ->orWhereIn('tipo', ['reclamo', 'seguridad', 'pago']);
    }

    public function scopeRecientes($query, $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeProgramadas($query)
    {
        return $query->whereNotNull('programada_para')
            ->where('enviada', false)
            ->where('programada_para', '<=', now());
    }

    // Métodos estáticos para crear notificaciones comunes
    public static function crearNuevoPedido($userId, $pedidoId, $numeroPedido)
    {
        return self::create([
            'user_id' => $userId,
            'titulo' => 'Nuevo pedido',
            'mensaje' => "Tu pedido {$numeroPedido} ha sido creado exitosamente",
            'tipo' => 'pedido',
            'data' => ['pedido_id' => $pedidoId, 'numero_pedido' => $numeroPedido],
            'leida' => false,
            'prioridad' => 2,
        ]);
    }

    public static function crearPagoConfirmado($userId, $pedidoId, $numeroPedido)
    {
        return self::create([
            'user_id' => $userId,
            'titulo' => 'Pago confirmado',
            'mensaje' => "El pago de tu pedido {$numeroPedido} ha sido confirmado",
            'tipo' => 'pago',
            'data' => ['pedido_id' => $pedidoId, 'numero_pedido' => $numeroPedido],
            'leida' => false,
            'prioridad' => 1,
        ]);
    }

    public static function crearEnvioEnCamino($userId, $pedidoId, $numeroPedido, $codigoSeguimiento = null)
    {
        $mensaje = "Tu pedido {$numeroPedido} ha sido enviado";
        if ($codigoSeguimiento) {
            $mensaje .= ". Código de seguimiento: {$codigoSeguimiento}";
        }
        
        return self::create([
            'user_id' => $userId,
            'titulo' => 'Pedido en camino',
            'mensaje' => $mensaje,
            'tipo' => 'envio',
            'data' => [
                'pedido_id' => $pedidoId, 
                'numero_pedido' => $numeroPedido,
                'codigo_seguimiento' => $codigoSeguimiento
            ],
            'leida' => false,
            'prioridad' => 2,
        ]);
    }

    public static function crearReclamoCreado($userId, $reclamoId, $codigoReclamo)
    {
        return self::create([
            'user_id' => $userId,
            'titulo' => 'Reclamo registrado',
            'mensaje' => "Tu reclamo {$codigoReclamo} ha sido registrado exitosamente",
            'tipo' => 'reclamo',
            'data' => ['reclamo_id' => $reclamoId, 'codigo_reclamo' => $codigoReclamo],
            'leida' => false,
            'prioridad' => 1,
        ]);
    }

    public static function crearPromocionEspecial($userId, $titulo, $mensaje, $codigoCupon = null)
    {
        $data = [];
        if ($codigoCupon) {
            $data['cupon_codigo'] = $codigoCupon;
        }
        
        return self::create([
            'user_id' => $userId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => 'promocion',
            'data' => $data,
            'leida' => false,
            'prioridad' => 3,
        ]);
    }

    public static function crearAlertaSeguridad($userId, $titulo, $mensaje)
    {
        return self::create([
            'user_id' => $userId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => 'seguridad',
            'data' => [],
            'leida' => false,
            'prioridad' => 1,
        ]);
    }

    // Método para enviar notificaciones en lote
    public static function enviarNotificacionesMasivas($userIds, $titulo, $mensaje, $tipo = 'sistema', $data = null)
    {
        $notificaciones = [];
        
        foreach ($userIds as $userId) {
            $notificaciones[] = [
                'user_id' => $userId,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo,
                'data' => $data,
                'leida' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return self::insert($notificaciones);
    }
}