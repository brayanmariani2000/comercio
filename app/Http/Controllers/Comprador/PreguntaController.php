<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\Pregunta;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PreguntaController extends Controller
{
    public function store(Request $request, $productoId)
    {
        $validator = Validator::make($request->all(), [
            'pregunta' => 'required|string|min:10',
            'anonima' => 'boolean'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $pregunta = Pregunta::create([
            'user_id' => $request->user()->id,
            'producto_id' => $productoId,
            'pregunta' => $request->pregunta,
            'anonima' => $request->anonima ?? false
        ]);

        return response()->json(['success' => true, 'pregunta' => $pregunta], 201);
    }

    public function vote(Request $request, $preguntaId)
    {
        $pregunta = Pregunta::findOrFail($preguntaId);
        $pregunta->agregarVoto($request->user()->id, $request->util ?? true);
        return response()->json(['success' => true, 'mensaje' => 'Voto registrado']);
    }
}