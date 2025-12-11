<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['user', 'vendedor', 'items.producto']);
        if ($request->filled('estado_pedido')) {
            $query->where('estado_pedido', $request->estado_pedido);
        }
        if ($request->filled('estado_pago')) {
            $query->where('estado_pago', $request->estado_pago);
        }
        if ($request->filled('search')) {
            $query->where('numero_pedido', 'LIKE', "%{$request->search}%")
                 ->orWhere('codigo_qr', 'LIKE', "%{$request->search}%")
                 ->orWhere('serial_compra', 'LIKE', "%{$request->search}%");
        }
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $pedidos = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json([
            'success' => true,
            'pedidos' => $pedidos,
            'estadisticas' => [
                'total' => Pedido::count(),
                'pendientes' => Pedido::where('estado_pedido', 'pendiente')->count(),
                'entregados' => Pedido::where('estado_pedido', 'entregado')->count(),
                'cancelados' => Pedido::where('estado_pedido', 'cancelado')->count(),
                'reclamados' => Pedido::where('estado_pedido', 'reclamado')->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $pedido = Pedido::with([
            'user',
            'vendedor',
            'items.producto',
            'reclamos',
            'validaciones',
            'comision'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'pedido' => $pedido,
            'datos_impresion' => $pedido->datosParaImpresion(),
            'qr_base64' => $pedido->generarQRImagen(300)
        ]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->actualizarEstado($request->estado, $request->comentario ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'pedido' => $pedido->fresh()
        ]);
    }

    public function cancelar($id, Request $request)
    {
        $pedido = Pedido::findOrFail($id);
        if (!in_array($pedido->estado_pedido, ['pendiente', 'confirmado'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede cancelar pedidos pendientes o confirmados'
            ], 400);
        }

        $pedido->actualizarEstado('cancelado', $request->motivo ?? 'Cancelado por administrador');
        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado',
            'pedido' => $pedido->fresh()
        ]);
    }

    public function facturar($id)
    {
        $pedido = Pedido::findOrFail($id);
        if ($pedido->estado_pago !== 'confirmado') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden facturar pedidos con pago confirmado'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'factura' => $pedido->generarFactura()
        ]);
    }

    public function validarPedido(Request $request)
    {
        $pedido = Pedido::where('codigo_qr', $request->codigo_qr)
            ->orWhere('serial_compra', $request->serial_compra)
            ->first();

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Código no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'valido' => true,
            'pedido' => $pedido->load(['user', 'vendedor', 'items.producto'])
        ]);
    }
}