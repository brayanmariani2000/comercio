<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductoAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['vendedor.user', 'categoria']);
        if ($request->has('estado')) {
            if ($request->estado === 'aprobados') {
                $query->where('aprobado', true)->where('activo', true);
            } elseif ($request->estado === 'pendientes') {
                $query->where('aprobado', false);
            } elseif ($request->estado === 'rechazados') {
                $query->where('aprobado', false)->whereNotNull('razon_rechazo');
            } elseif ($request->estado === 'inactivos') {
                $query->where('activo', false);
            }
        }
        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%")
                 ->orWhere('codigo', 'LIKE', "%{$request->search}%")
                 ->orWhere('sku', 'LIKE', "%{$request->search}%");
        }
        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $productos = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'estadisticas' => [
                'total' => Producto::count(),
                'aprobados' => Producto::where('aprobado', true)->count(),
                'pendientes' => Producto::where('aprobado', false)->count(),
                'activos' => Producto::where('activo', true)->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $producto = Producto::with([
            'vendedor.user',
            'categoria',
            'imagenes',
            'resenas.user',
            'preguntas.respuestas'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'producto' => $producto
        ]);
    }

    public function aprobar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        DB::beginTransaction();
        try {
            $producto->aprobado = true;
            $producto->activo = true;
            $producto->save();

            // Notificar al vendedor
            $producto->vendedor->user->generarNotificacion(
                'Producto aprobado',
                "Tu producto '{$producto->nombre}' ha sido aprobado y está visible en la tienda.",
                'producto',
                ['producto_id' => $producto->id]
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Producto aprobado exitosamente',
                'producto' => $producto
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rechazar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'razon' => 'required|string|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $producto = Producto::findOrFail($id);
        DB::beginTransaction();
        try {
            $producto->aprobado = false;
            $producto->activo = false;
            $producto->razon_rechazo = $request->razon;
            $producto->save();

            $producto->vendedor->user->generarNotificacion(
                'Producto rechazado',
                "Tu producto '{$producto->nombre}' fue rechazado. Razón: {$request->razon}",
                'producto',
                ['producto_id' => $producto->id]
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Producto rechazado exitosamente',
                'producto' => $producto
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        // Verificar que no tenga pedidos activos
        $tienePedidos = $producto->pedidoItems()->whereIn('estado_pedido', ['pendiente', 'confirmado', 'preparando', 'enviado'])->exists();
        if ($tienePedidos) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un producto con pedidos activos'
            ], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($producto->imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->imagen);
            }
            $producto->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado permanentemente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleActivo($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->activo = !$producto->activo;
        $producto->save();

        return response()->json([
            'success' => true,
            'activo' => $producto->activo,
            'message' => $producto->activo ? 'Producto activado' : 'Producto desactivado'
        ]);
    }
}