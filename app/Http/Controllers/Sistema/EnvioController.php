<?php
namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\MetodoEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnvioController extends Controller
{
    public function metodos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'estado' => ['required', 'string'],
            'ciudad' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $metodos = MetodoEnvio::activos()
            ->where(function($q) use ($request) {
                $q->whereNull('zonas_cobertura')
                  ->orWhereJsonContains('zonas_cobertura', [['estado' => $request->estado]]);
            })
            ->get();

        return response()->json([
            'success' => true,
            'metodos' => $metodos
        ]);
    }

    public function calcular(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'metodo_envio_id' => ['required', 'exists:metodos_envio,id'],
            'subtotal' => ['required', 'numeric'],
            'peso' => ['nullable', 'numeric'],
            'distancia' => ['nullable', 'numeric'],
            'seguro' => ['boolean']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $metodo = MetodoEnvio::findOrFail($request->metodo_envio_id);
        $costo = $metodo->calcularCosto($request->subtotal, $request->all());

        return response()->json([
            'success' => true,
            'costo' => $costo,
            'metodo' => $metodo->nombre,
            'dias_entrega' => "{$metodo->dias_entrega_min} - {$metodo->dias_entrega_max} días"
        ]);
    }
}