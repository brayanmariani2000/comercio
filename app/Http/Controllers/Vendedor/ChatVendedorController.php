<?php
namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ChatVendedorController extends Controller
{
    public function index(Request $request)
    {
        $vendedor = $request->user()->vendedor;

        $conversaciones = $vendedor->conversaciones()
            ->with(['user', 'producto', 'pedido', 'ultimoMensaje'])
            ->orderBy('ultimo_mensaje_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'conversaciones' => $conversaciones
        ]);
    }

    public function show(Request $request, $id)
    {
        $vendedor = $request->user()->vendedor;
        $conversacion = $vendedor->conversaciones()
            ->with(['mensajes.user', 'mensajes.vendedor', 'user', 'producto'])
            ->findOrFail($id);

        $conversacion->marcarComoLeida($request->user()->id, $vendedor->id);

        return response()->json([
            'success' => true,
            'conversacion' => $conversacion
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'mensaje' => ['required', 'string', 'min:1'],
            'adjuntos' => ['nullable', 'array'],
            'adjuntos.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $vendedor = $request->user()->vendedor;
        $conversacion = $vendedor->conversaciones()->findOrFail($id);

        $adjuntos = [];
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                $path = $file->store('mensajes/' . $conversacion->id, 'public');
                $adjuntos[] = [
                    'path' => $path,
                    'nombre' => $file->getClientOriginalName(),
                    'tipo' => $file->getMimeType()
                ];
            }
        }

        $mensaje = $conversacion->enviarMensaje(
            $request->mensaje,
            null,
            $vendedor->id,
            $adjuntos
        );

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje->load('vendedor')
        ]);
    }

    public function close(Request $request, $id)
    {
        $vendedor = $request->user()->vendedor;
        $conversacion = $vendedor->conversaciones()->findOrFail($id);

        $conversacion->cerrar($request->user()->id, $request->motivo ?? 'Cerrado por vendedor');

        return response()->json([
            'success' => true,
            'message' => 'Conversación cerrada'
        ]);
    }

    public function reopen($id)
    {
        $vendedor = request()->user()->vendedor;
        $conversacion = $vendedor->conversaciones()->findOrFail($id);
        $conversacion->reabrir();
        return response()->json(['success' => true, 'message' => 'Conversación reabierta']);
    }
}