<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Pedido;
use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\DireccionEnvio;
use App\Models\MetodoEnvio;

class PedidoApiController extends ApiController
{
    public function index(Request $request)
    {
        $pedidos = $request->user()->pedidos()
            ->with(['vendedor', 'items.producto'])
            ->paginate(15);

        return $this->success($pedidos);
    }

    public function show($id)
    {
        $pedido = $request->user()->pedidos()
            ->with(['vendedor', 'items.producto', 'metodoEnvio'])
            ->findOrFail($id);

        return $this->success([
            'pedido' => $pedido,
            'qr' => $pedido->generarQRImagen(150),
            'serial' => $pedido->serial_compra,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'direccion_envio_id' => 'required|exists:direcciones_envio,id',
            'metodo_envio_id' => 'required|exists:metodos_envio,id',
            'metodo_pago' => 'required|in:transferencia_bancaria,pago_movil,efectivo',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = $request->user();
        $carrito = $user->carrito;
        if (!$carrito || $carrito->items()->count() === 0) {
            return $this->error('El carrito está vacío');
        }

        $direccion = DireccionEnvio::findOrFail($request->direccion_envio_id);
        if ($direccion->user_id !== $user->id) {
            return $this->unauthorized('Dirección no autorizada');
        }

        $metodoEnvio = MetodoEnvio::findOrFail($request->metodo_envio_id);
        if (!$metodoEnvio->cubreUbicacion($direccion->estado->nombre, $direccion->ciudad->nombre)) {
            return $this->error('Método de envío no disponible para tu ubicación');
        }

        try {
            $datosPedido = [
                'direccion_envio' => $direccion->direccion_completa,
                'ciudad_envio' => $direccion->ciudad->nombre,
                'estado_envio' => $direccion->estado->nombre,
                'metodo_pago' => $request->metodo_pago,
                'metodo_envio_id' => $request->metodo_envio_id,
                'nombre_cliente' => $user->name,
                'cedula_cliente' => $user->cedula,
                'telefono_cliente' => $user->telefono,
                'email_cliente' => $user->email,
            ];

            $pedido = $user->crearPedido($datosPedido);

            return $this->success([
                'pedido' => $pedido->load(['items.producto']),
                'qr' => $pedido->codigo_qr,
                'serial' => $pedido->serial_compra,
                'numero_pedido' => $pedido->numero_pedido,
            ], 'Pedido creado exitosamente', 201);

        } catch (\Exception $e) {
            return $this->error('Error al crear pedido: ' . $e->getMessage(), 500);
        }
    }

    public function confirmarPago(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'referencia_pago' => 'required|string',
            'banco' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $pedido = $request->user()->pedidos()->findOrFail($id);
        if ($pedido->estado_pago !== 'pendiente') {
            return $this->error('El pago ya fue procesado');
        }

        $pedido->referencia_pago = $request->referencia_pago;
        $pedido->banco = $request->banco;
        $pedido->estado_pago = 'verificando';
        $pedido->save();

        return $this->success(null, 'Pago enviado para verificación');
    }

    public function validar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_qr' => 'nullable|string',
            'serial_compra' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $pedido = null;
        if ($request->filled('codigo_qr')) {
            $pedido = Pedido::where('codigo_qr', $request->codigo_qr)->first();
        } elseif ($request->filled('serial_compra')) {
            $pedido = Pedido::where('serial_compra', $request->serial_compra)->first();
        }

        if (!$pedido) {
            return $this->notFound('Código no encontrado');
        }

        // Solo permitir validación si el usuario es dueño del pedido o es admin/vendedor
        $user = $request->user();
        if ($user && ($user->id === $pedido->user_id || $user->esAdministrador() || ($user->esVendedor() && $user->vendedor->id === $pedido->vendedor_id))) {
            $valido = true;
        } else {
            $valido = false;
        }

        return $this->success([
            'valido' => $valido,
            'pedido' => $valido ? [
                'id' => $pedido->id,
                'numero' => $pedido->numero_pedido,
                'cliente' => $pedido->nombre_cliente,
                'vendedor' => $pedido->vendedor->nombre_comercial,
                'total' => $pedido->total,
                'estado' => $pedido->estado_pedido,
            ] : null,
        ]);
    }
}