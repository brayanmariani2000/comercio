<?php
namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function confirmar(Request $request, $pedidoId)
    {
        $validator = Validator::make($request->all(), [
            'referencia' => ['required', 'string'],
            'banco' => ['nullable', 'string'],
            'comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $pedido = $user->pedidos()->findOrFail($pedidoId);

        if ($pedido->estado_pago !== 'pendiente') {
            return response()->json(['success' => false, 'message' => 'El pago ya fue confirmado o procesado'], 400);
        }

        DB::beginTransaction();
        try {
            $pedido->referencia_pago = $request->referencia;
            $pedido->banco = $request->banco;
            $pedido->estado_pago = 'verificando';
            $pedido->save();

            // Aquí iría el guardado del comprobante si se usa Storage

            // Notificación al vendedor
            $pedido->vendedor->user->generarNotificacion(
                'Pago pendiente de verificación',
                "El cliente ha enviado la referencia para el pedido {$pedido->numero_pedido}",
                'pago',
                ['pedido_id' => $pedido->id]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago confirmado. Esperando verificación por parte del vendedor.',
                'pedido' => $pedido->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al confirmar pago: ' . $e->getMessage()], 500);
        }
    }
}