<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\DireccionEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DireccionController extends Controller
{
    public function index(Request $request)
    {
        $direcciones = $request->user()->direccionesEnvio()->activas()->orderBy('principal', 'desc')->get();
        return response()->json(['success' => true, 'direcciones' => $direcciones]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alias' => 'nullable|string',
            'nombre_completo' => 'required|string',
            'cedula' => 'required|string',
            'telefono' => 'required|string',
            'estado_id' => 'required|exists:estados_venezuela,id',
            'ciudad_id' => 'required|exists:ciudades_venezuela,id',
            'codigo_postal' => 'nullable|string',
            'instrucciones' => 'nullable|string',
            'principal' => 'boolean',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        if ($request->principal) {
            $direccion = DireccionEnvio::create($data);
            $direccion->marcarComoPrincipal();
        } else {
            $direccion = DireccionEnvio::create($data);
        }

        return response()->json(['success' => true, 'direccion' => $direccion], 201);
    }

    public function update(Request $request, $id)
    {
        $direccion = $request->user()->direccionesEnvio()->findOrFail($id);
        $direccion->update($request->only([
            'alias', 'nombre_completo', 'cedula', 'telefono', 'direccion',
            'estado_id', 'ciudad_id', 'codigo_postal', 'instrucciones', 'activo'
        ]));

        if ($request->principal) {
            $direccion->marcarComoPrincipal();
        }

        return response()->json(['success' => true, 'direccion' => $direccion]);
    }

    public function destroy(Request $request, $id)
    {
        $direccion = $request->user()->direccionesEnvio()->findOrFail($id);
        $direccion->delete();
        return response()->json(['success' => true, 'message' => 'Dirección eliminada']);
    }
}