<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\Resena;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ResenaController extends Controller
{
    public function store(Request $request, $productoId)
    {
        $user = $request->user();
        $producto = Producto::findOrFail($productoId);

        // Verificar compra previa
        $compraVerificada = $user->pedidos()
            ->whereHas('items', fn($q) => $q->where('producto_id', $productoId))
            ->where('estado_pedido', 'entregado')
            ->exists();

        $validator = Validator::make($request->all(), [
            'calificacion' => 'required|integer|between:1,5',
            'comentario' => 'required|string|min:10',
            'titulo' => 'nullable|string',
            'ventajas' => 'nullable|array',
            'desventajas' => 'nullable|array',
            'recomendado' => 'boolean',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $resena = Resena::create([
            'user_id' => $user->id,
            'producto_id' => $productoId,
            'calificacion' => $request->calificacion,
            'comentario' => $request->comentario,
            'titulo' => $request->titulo,
            'ventajas' => $request->ventajas,
            'desventajas' => $request->desventajas,
            'recomendado' => $request->recomendado ?? true,
            'verificada_compra' => $compraVerificada,
            'aprobada' => true, // o false si requiere moderación
        ]);

        $producto->actualizarRating();

        return response()->json(['success' => true, 'resena' => $resena], 201);
    }

    public function like(Request $request, $resenaId)
    {
        $resena = Resena::findOrFail($resenaId);
        $likes = $resena->agregarLike($request->user()->id);
        return response()->json(['success' => true, 'likes' => $likes]);
    }
}