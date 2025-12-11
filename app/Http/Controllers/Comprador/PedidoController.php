<?php

namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\MetodoEnvio;
use App\Models\DireccionEnvio;
use App\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    /**
     * Listar pedidos del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = $user->pedidos()->with(['vendedor', 'items.producto']);
        
        // Filtrar por estado
        if ($request->has('estado')) {
            $query->where('estado_pedido', $request->estado);
        }
        
        // Filtrar por fecha
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
        }
        
        // Ordenar
        $orden = $request->get('orden', 'recientes');
        if ($orden === 'recientes') {
            $query->orderBy('created_at', 'desc');
        } elseif ($orden === 'antiguos') {
            $query->orderBy('created_at', 'asc');
        }
        
        $pedidos = $query->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'pedidos' => $pedidos,
            'estadisticas' => [
                'total' => $user->total_compras,
                'pendientes' => $user->pedidos()->where('estado_pedido', 'pendiente')->count(),
                'en_proceso' => $user->pedidos()->whereIn('estado_pedido', ['confirmado', 'preparando', 'enviado'])->count(),
                'completados' => $user->pedidos()->where('estado_pedido', 'entregado')->count(),
                'cancelados' => $user->pedidos()->where('estado_pedido', 'cancelado')->count(),
            ]
        ]);
    }

    /**
     * Mostrar detalle de un pedido
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $pedido = $user->pedidos()
            ->with([
                'vendedor',
                'items.producto.imagenes',
                'metodoEnvio',
                'reclamos',
                'validaciones'
            ])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'pedido' => $pedido,
            'datos_impresion' => $pedido->datosParaImpresion(),
            'qr_base64' => $pedido->generarQRImagen(200),
            'barcode_base64' => $pedido->generarCodigoBarras(),
            'factura' => $pedido->generarFactura(),
        ]);
    }

    /**
     * Crear un nuevo pedido desde el carrito
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'direccion_envio_id' => ['required', 'exists:direcciones_envio,id'],
            'metodo_envio_id' => ['required', 'exists:metodos_envio,id'],
            'metodo_pago' => ['required', 'in:transferencia_bancaria,pago_movil,efectivo,tarjeta_debito,tarjeta_credito,paypal,zelle,binance'],
            'cupon_codigo' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],
            'seguro_envio' => ['boolean'],
            'direccion_facturacion' => ['nullable', 'string'],
            'rif_facturacion' => ['nullable', 'string'],
            'razon_social_facturacion' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito || $carrito->items()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'El carrito está vacío'
            ], 400);
        }

        // Verificar disponibilidad de productos
        $verificacion = $carrito->verificarDisponibilidad();
        if (!$verificacion['disponible']) {
            return response()->json([
                'success' => false,
                'message' => 'Algunos productos no están disponibles',
                'productos_no_disponibles' => $verificacion['productos_no_disponibles']
            ], 400);
        }

        // Obtener dirección de envío
        $direccion = DireccionEnvio::findOrFail($request->direccion_envio_id);
        
        // Verificar que la dirección pertenezca al usuario
        if ($direccion->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Dirección de envío inválida'
            ], 403);
        }

        // Obtener método de envío
        $metodoEnvio = MetodoEnvio::findOrFail($request->metodo_envio_id);
        
        // Verificar que el método cubra la ubicación
        if (!$metodoEnvio->cubreUbicacion($direccion->estado->nombre, $direccion->ciudad->nombre)) {
            return response()->json([
                'success' => false,
                'message' => 'El método de envío no cubre tu ubicación'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Preparar datos del pedido
            $datosPedido = [
                'direccion_envio' => $direccion->direccion_completa,
                'ciudad_envio' => $direccion->ciudad->nombre,
                'estado_envio' => $direccion->estado->nombre,
                'metodo_pago' => $request->metodo_pago,
                'metodo_envio_id' => $request->metodo_envio_id,
                'cupon_codigo' => $request->cupon_codigo,
                'seguro_envio' => $request->seguro_envio ?? false,
                'direccion_facturacion' => $request->direccion_facturacion,
                'rif_facturacion' => $request->rif_facturacion,
                'razon_social_facturacion' => $request->razon_social_facturacion,
                'notas' => $request->notas,
                'nombre_cliente' => $user->name,
                'cedula_cliente' => $user->cedula,
                'telefono_cliente' => $user->telefono,
                'email_cliente' => $user->email,
            ];

            // Crear pedido
            $pedido = $user->crearPedido($datosPedido);

            // Registrar en bitácora
            BitacoraSistema::registrarCreacion(
                $user->id,
                'Pedido',
                $pedido->id,
                $pedido->toArray()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'pedido' => $pedido->load(['vendedor', 'items.producto']),
                'qr' => $pedido->codigo_qr,
                'serial' => $pedido->serial_compra,
                'instrucciones_pago' => $this->getInstruccionesPago($request->metodo_pago),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar pedido
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($id);

        // Verificar que se pueda cancelar
        if (!in_array($pedido->estado_pedido, ['pendiente', 'confirmado'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar el pedido en su estado actual'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $pedido->actualizarEstado('cancelado', $request->motivo ?? 'Cancelado por el cliente');

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'cancelar_pedido',
                'Pedido',
                $pedido->id,
                'Pedido cancelado por el cliente',
                ['estado_anterior' => $pedido->getOriginal('estado_pedido')],
                ['estado_nuevo' => 'cancelado', 'motivo' => $request->motivo]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado exitosamente',
                'pedido' => $pedido->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar pago del pedido
     */
    public function confirmPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'referencia_pago' => ['required', 'string'],
            'banco' => ['nullable', 'string'],
            'monto' => ['required', 'numeric'],
            'fecha_pago' => ['required', 'date'],
            'comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($id);

        // Verificar que el pedido esté pendiente de pago
        if ($pedido->estado_pago !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'El pago ya ha sido procesado'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Actualizar información de pago
            $pedido->referencia_pago = $request->referencia_pago;
            $pedido->banco = $request->banco;
            $pedido->estado_pago = 'verificando';
            $pedido->fecha_pago = $request->fecha_pago;
            $pedido->save();

            // Subir comprobante si existe
            if ($request->hasFile('comprobante')) {
                $path = $request->file('comprobante')->store('comprobantes/pagos/' . $pedido->id, 'public');
                // Guardar path en la base de datos
            }

            // Registrar en bitácora
            BitacoraSistema::registrarActualizacion(
                $user->id,
                'Pedido',
                $pedido->id,
                ['estado_pago' => 'pendiente'],
                ['estado_pago' => 'verificando', 'referencia_pago' => $request->referencia_pago]
            );

            // Notificar al vendedor
            $pedido->vendedor->user->generarNotificacion(
                'Pago pendiente de verificación',
                "El cliente ha enviado el comprobante de pago para el pedido {$pedido->numero_pedido}",
                'pago',
                ['pedido_id' => $pedido->id, 'referencia' => $request->referencia_pago]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comprobante de pago enviado. Espera la verificación.',
                'pedido' => $pedido->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar factura del pedido
     */
    public function downloadInvoice(Request $request, $id)
    {
        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($id);

        // Verificar que el pedido tenga pago confirmado
        if ($pedido->estado_pago !== 'confirmado') {
            return response()->json([
                'success' => false,
                'message' => 'La factura solo está disponible para pedidos pagados'
            ], 400);
        }

        try {
            $factura = $pedido->generarFactura();
            
            // Generar PDF de la factura (usar DomPDF o similar)
            // $pdf = PDF::loadView('factura', $factura);
            
            return response()->json([
                'success' => true,
                'factura' => $factura,
                'download_url' => route('pedidos.factura.pdf', $pedido->id), // Ruta para descargar PDF
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Imprimir etiqueta del pedido
     */
    public function printLabel(Request $request, $id)
    {
        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($id);

        $datosImpresion = $pedido->datosParaImpresion();

        return response()->json([
            'success' => true,
            'datos_impresion' => $datosImpresion,
            'qr_image' => $pedido->generarQRImagen(300),
            'barcode_image' => $pedido->generarCodigoBarras(),
        ]);
    }

    /**
     * Seguimiento del pedido
     */
    public function track(Request $request, $id)
    {
        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($id);

        $historial = [
            ['estado' => 'pendiente', 'fecha' => $pedido->created_at, 'descripcion' => 'Pedido creado'],
        ];

        if ($pedido->estado_pago === 'confirmado') {
            $historial[] = ['estado' => 'pago_confirmado', 'fecha' => $pedido->fecha_pago, 'descripcion' => 'Pago confirmado'];
        }

        if ($pedido->estado_pedido === 'confirmado') {
            $historial[] = ['estado' => 'confirmado', 'fecha' => $pedido->updated_at, 'descripcion' => 'Pedido confirmado por el vendedor'];
        }

        if ($pedido->estado_pedido === 'preparando') {
            $historial[] = ['estado' => 'preparando', 'fecha' => $pedido->updated_at, 'descripcion' => 'Pedido en preparación'];
        }

        if ($pedido->estado_pedido === 'enviado') {
            $historial[] = [
                'estado' => 'enviado', 
                'fecha' => $pedido->fecha_envio, 
                'descripcion' => 'Pedido enviado',
                'codigo_seguimiento' => $pedido->codigo_seguimiento,
                'enlace_seguimiento' => $pedido->metodoEnvio ? $pedido->metodoEnvio->obtenerEnlaceSeguimiento($pedido->codigo_seguimiento) : null
            ];
        }

        if ($pedido->estado_pedido === 'entregado') {
            $historial[] = ['estado' => 'entregado', 'fecha' => $pedido->fecha_entrega, 'descripcion' => 'Pedido entregado'];
        }

        return response()->json([
            'success' => true,
            'pedido' => $pedido->only(['id', 'numero_pedido', 'estado_pedido', 'estado_pago']),
            'historial' => $historial,
            'fecha_estimada_entrega' => $pedido->fecha_estimada_entrega,
            'dias_transcurridos' => $pedido->created_at->diffInDays(now()),
        ]);
    }

    /**
     * Calcular total del pedido antes de crear
     */
    public function calculateTotal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'direccion_envio_id' => ['required', 'exists:direcciones_envio,id'],
            'metodo_envio_id' => ['required', 'exists:metodos_envio,id'],
            'cupon_codigo' => ['nullable', 'string'],
            'seguro_envio' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito || $carrito->items()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'El carrito está vacío'
            ], 400);
        }

        try {
            $direccion = DireccionEnvio::findOrFail($request->direccion_envio_id);
            $metodoEnvio = MetodoEnvio::findOrFail($request->metodo_envio_id);

            // Calcular subtotal
            $subtotal = $carrito->calcularSubtotal();

            // Aplicar cupón si existe
            $descuentoCupon = 0;
            if ($request->cupon_codigo) {
                $cupon = Cupon::validar($request->cupon_codigo, $user->id, $subtotal);
                if ($cupon) {
                    $descuentoCupon = $cupon->calcularDescuento($subtotal);
                    $subtotal -= $descuentoCupon;
                }
            }

            // Calcular envío
            $envio = $carrito->calcularEnvio($request->metodo_envio_id, [
                'estado' => $direccion->estado->nombre,
                'ciudad' => $direccion->ciudad->nombre,
                'seguro' => $request->seguro_envio ?? false,
            ]);

            // Calcular IVA (16% Venezuela)
            $iva = $subtotal * 0.16;

            $total = $subtotal + $iva + $envio;

            return response()->json([
                'success' => true,
                'subtotal' => $subtotal,
                'descuento_cupon' => $descuentoCupon,
                'envio' => $envio,
                'iva' => $iva,
                'total' => $total,
                'metodo_envio' => $metodoEnvio->nombre,
                'dias_entrega' => $metodoEnvio->dias_entrega_min . ' - ' . $metodoEnvio->dias_entrega_max . ' días',
                'cupon_valido' => isset($cupon),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular total: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener instrucciones de pago según el método
     */
    private function getInstruccionesPago($metodoPago)
    {
        $instrucciones = [
            'transferencia_bancaria' => [
                'titulo' => 'Transferencia Bancaria',
                'pasos' => [
                    'Realiza la transferencia a nuestra cuenta bancaria',
                    'Envía el comprobante a través de esta plataforma',
                    'Espera la confirmación del pago (24-48 horas)',
                ],
                'datos_bancarios' => [
                    'banco' => 'Banco de Venezuela',
                    'tipo_cuenta' => 'Corriente',
                    'numero_cuenta' => '0102-XXXX-XXXX-XXXX-XXXX',
                    'titular' => 'MERCADO ELECTRÓNICO C.A.',
                    'rif' => 'J-XXXXXXXX-X',
                ]
            ],
            'pago_movil' => [
                'titulo' => 'Pago Móvil',
                'pasos' => [
                    'Registra nuestro número en tu app de Pago Móvil',
                    'Realiza el pago con tu cédula/RIF',
                    'Envía el comprobante con la referencia',
                ],
                'datos' => [
                    'telefono' => '0412-XXX-XXXX',
                    'cedula' => 'V-XXXXXXXX',
                    'banco' => 'Banco de Venezuela',
                ]
            ],
            'efectivo' => [
                'titulo' => 'Pago en Efectivo',
                'pasos' => [
                    'Acércate a nuestra oficina principal',
                    'Realiza el pago en efectivo',
                    'Recibe tu comprobante de pago',
                ],
                'direccion' => 'Av. Principal, Caracas, Venezuela'
            ],
            // ... otros métodos
        ];

        return $instrucciones[$metodoPago] ?? ['titulo' => 'Método de pago', 'pasos' => ['Contacta con soporte para instrucciones']];
    }

    /**
     * Calcular fecha estimada de entrega
     */
    public function calculateDeliveryDate(Request $request, $id)
    {
        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($id);

        if (!$pedido->metodo_envio_id) {
            return response()->json([
                'success' => false,
                'message' => 'El pedido no tiene método de envío asignado'
            ], 400);
        }

        $metodoEnvio = $pedido->metodoEnvio;
        $fechaCalculo = $pedido->fecha_envio ?? $pedido->created_at;

        $fechaEstimada = $metodoEnvio->calcularFechaEntrega($fechaCalculo);

        return response()->json([
            'success' => true,
            'fecha_envio' => $pedido->fecha_envio,
            'fecha_estimada' => $fechaEstimada,
            'dias_restantes' => now()->diffInDays($fechaEstimada['fecha_maxima'], false),
        ]);
    }
}