<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ChatCompradorController extends Controller
{
    public function index(Request $request)
    {
        $conversaciones = $request->user()->conversacionesComoComprador()
            ->with(['vendedor', 'producto', 'pedido', 'ultimoMensaje'])
            ->orderBy('ultimo_mensaje_at', 'desc')
            ->paginate(20);

        return response()->json(['success' => true, 'conversaciones' => $conversaciones]);
    }

    public function show(Request $request, $id)
    {
        $conversacion = $request->user()->conversacionesComoComprador()
            ->with(['mensajes.user', 'mensajes.vendedor', 'vendedor', 'producto'])
            ->findOrFail($id);

        // Marcar mensajes como leídos
        $conversacion->marcarComoLeida($request->user()->id);

        return response()->json(['success' => true, 'conversacion' => $conversacion]);
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

        $conversacion = $request->user()->conversacionesComoComprador()->findOrFail($id);

        // Subir adjuntos
        $adjuntos = [];
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                $path = $file->store('mensajes/' . $conversacion->id, 'public');
                $adjuntos[] = ['path' => $path, 'nombre' => $file->getClientOriginalName(), 'tipo' => $file->getMimeType()];
            }
        }

        $mensaje = $conversacion->enviarMensaje($request->mensaje, $request->user()->id, null, $adjuntos);

        return response()->json(['success' => true, 'mensaje' => $mensaje->load('user')]);
    }

    public function close(Request $request, $id)
    {
        $conversacion = $request->user()->conversacionesComoComprador()->findOrFail($id);
        $conversacion->cerrar($request->user()->id, $request->motivo);
        return response()->json(['success' => true, 'message' => 'Conversación cerrada']);
    }
}