<?php
namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\BitacoraSistema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PedidoVendedorController extends Controller
{
    public function index(Request $request)
    {
        $vendedor = $request->user()->vendedor;
        $query = $vendedor->pedidos()->with(['user', 'items.producto', 'reclamos']);

        if ($request->filled('estado')) {
            $query->where('estado_pedido', $request->estado);
        }

        if ($request->filled('search')) {
            $query->where('numero_pedido', 'LIKE', "%{$request->search}%")
                ->orWhere('nombre_cliente', 'LIKE', "%{$request->search}%")
                ->orWhere('cedula_cliente', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $pedidos = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'pedidos' => $pedidos,
            'estadisticas' => [
                'total' => $vendedor->pedidos()->count(),
                'pendientes' => $vendedor->pedidos()->where('estado_pedido', 'pendiente')->count(),
                'preparando' => $vendedor->pedidos()->where('estado_pedido', 'preparando')->count(),
                'enviados' => $vendedor->pedidos()->where('estado_pedido', 'enviado')->count(),
                'entregados' => $vendedor->pedidos()->where('estado_pedido', 'entregado')->count(),
                'reclamados' => $vendedor->pedidos()->where('estado_pedido', 'reclamado')->count(),
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $vendedor = $request->user()->vendedor;
        $pedido = $vendedor->pedidos()
            ->with(['user', 'items.producto', 'reclamos', 'validaciones'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'pedido' => $pedido,
            'datos_impresion' => $pedido->datosParaImpresion(),
            'qr_base64' => $pedido->generarQRImagen(200),
            'reclamos_activos' => $pedido->reclamos()->whereIn('estado', ['abierto', 'en_revision'])->get(),
        ]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'estado' => ['required', 'in:confirmado,preparando,enviado,entregado'],
            'codigo_seguimiento' => ['required_if:estado,enviado', 'nullable', 'string'],
            'fecha_envio' => ['nullable', 'date'],
            'comentario' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $vendedor = $request->user()->vendedor;
        $pedido = $vendedor->pedidos()->findOrFail($id);

        // Validar transiciones permitidas
        $transiciones = [
            'pendiente' => ['confirmado'],
            'confirmado' => ['preparando'],
            'preparando' => ['enviado'],
            'enviado' => ['entregado']
        ];

        if (!in_array($request->estado, $transiciones[$pedido->estado_pedido] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'Transición de estado no permitida'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($request->estado === 'enviado') {
                $pedido->codigo_seguimiento = $request->codigo_seguimiento;
                $pedido->fecha_envio = $request->fecha_envio ?? now();
            }

            $pedido->actualizarEstado($request->estado, $request->comentario);

            // Notificar al comprador
            $pedido->user->generarNotificacion(
                "Pedido actualizado",
                "Tu pedido {$pedido->numero_pedido} ahora está en estado: {$request->estado}",
                'pedido',
                ['pedido_id' => $pedido->id, 'estado' => $request->estado]
            );

            BitacoraSistema::registrar(
                $request->user()->id,
                'actualizar_estado_pedido_vendedor',
                'Pedido',
                $pedido->id,
                "Vendedor actualizó estado a {$request->estado}",
                ['estado_anterior' => $pedido->getOriginal('estado_pedido')],
                ['estado_nuevo' => $request->estado]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'pedido' => $pedido->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validarCompra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_qr' => ['required_without:serial_compra'],
            'serial_compra' => ['required_without:codigo_qr'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $pedido = Pedido::where('codigo_qr', $request->codigo_qr)
            ->orWhere('serial_compra', $request->serial_compra)
            ->first();

        if (!$pedido || $pedido->vendedor_id !== $request->user()->vendedor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado o no autorizado'
            ], 404);
        }

        $resultado = $pedido->validarCompra($request->codigo_qr, $request->serial_compra);

        return response()->json([
            'success' => true,
            'valido' => $resultado['valido'],
            'pedido' => $resultado['valido'] ? $pedido->load(['user']) : null,
            'mensaje' => $resultado['mensaje'],
        ]);
    }
}