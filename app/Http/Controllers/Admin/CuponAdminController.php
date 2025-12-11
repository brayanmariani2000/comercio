<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CuponAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Cupon::with('usos');
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }
        if ($request->filled('search')) {
            $query->where('codigo', 'LIKE', "%{$request->search}%")
                 ->orWhere('nombre', 'LIKE', "%{$request->search}%");
        }

        $cupones = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json([
            'success' => true,
            'cupones' => $cupones
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|unique:cupones',
            'nombre' => 'required|string',
            'tipo' => 'required|in:porcentaje,monto_fijo,envio_gratis',
            'valor' => 'required|numeric',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'usos_maximos' => 'nullable|integer|min:1',
            'minimo_compra' => 'nullable|numeric|min:0',
            'categorias_aplicables' => 'nullable|array',
            'productos_aplicables' => 'nullable|array',
            'usuarios_aplicables' => 'nullable|array',
            'solo_primer_compra' => 'boolean',
            'solo_usuarios_nuevos' => 'boolean',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cupon = Cupon::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cupón creado',
            'cupon' => $cupon
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $cupon = Cupon::findOrFail($id);
        $cupon->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cupón actualizado',
            'cupon' => $cupon
        ]);
    }

    public function toggleActivo($id)
    {
        $cupon = Cupon::findOrFail($id);
        $cupon->activo = !$cupon->activo;
        $cupon->save();

        return response()->json([
            'success' => true,
            'activo' => $cupon->activo
        ]);
    }

    public function estadisticas($id)
    {
        $cupon = Cupon::findOrFail($id);
        return response()->json([
            'success' => true,
            'estadisticas' => $cupon->obtenerEstadisticas()
        ]);
    }

    public function eliminar($id)
    {
        $cupon = Cupon::findOrFail($id);
        if ($cupon->usos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un cupón que ya ha sido usado'
            ], 400);
        }
        $cupon->delete();
        return response()->json([
            'success' => true,
            'message' => 'Cupón eliminado'
        ]);
    }
}