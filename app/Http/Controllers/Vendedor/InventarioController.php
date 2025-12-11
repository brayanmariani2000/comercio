<?php
namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $vendedor = $request->user()->vendedor;
        $query = $vendedor->productos()->with('categoria');

        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'sin_stock':
                    $query->where('stock', 0);
                    break;
                case 'bajo':
                    $query->whereRaw('stock <= stock_minimo')->where('stock', '>', 0);
                    break;
                case 'disponible':
                    $query->where('stock', '>', 0);
                    break;
            }
        }

        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%")
                ->orWhere('codigo', 'LIKE', "%{$request->search}%");
        }

        $productos = $query->paginate(25);

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'alertas' => [
                'sin_stock' => $vendedor->productos()->where('stock', 0)->count(),
                'bajo' => $vendedor->productos()->whereRaw('stock <= stock_minimo')->where('stock', '>', 0)->count(),
            ]
        ]);
    }

    public function ajustarStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stock' => ['required', 'integer', 'min:0'],
            'motivo' => ['required', 'string'],
            'tipo_ajuste' => ['required', 'in:entrada,salida,ajuste']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $vendedor = $request->user()->vendedor;
        $producto = $vendedor->productos()->findOrFail($id);

        DB::beginTransaction();
        try {
            $stockAnterior = $producto->stock;
            $cambio = 0;

            if ($request->tipo_ajuste === 'entrada') {
                $producto->stock += $request->stock;
                $cambio = $request->stock;
            } elseif ($request->tipo_ajuste === 'salida') {
                if ($producto->stock < $request->stock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuficiente para salida'
                    ], 400);
                }
                $producto->stock -= $request->stock;
                $cambio = -$request->stock;
            } else {
                $cambio = $request->stock - $stockAnterior;
                $producto->stock = $request->stock;
            }

            $producto->save();

            // Registrar en bitácora
            \App\Models\BitacoraSistema::registrar(
                $request->user()->id,
                'ajuste_inventario',
                'Producto',
                $producto->id,
                "Ajuste de stock: {$request->tipo_ajuste} de {$request->stock} unidades. Motivo: {$request->motivo}",
                ['stock_anterior' => $stockAnterior],
                ['stock_nuevo' => $producto->stock]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock ajustado correctamente',
                'producto' => $producto->fresh(),
                'cambio' => $cambio,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al ajustar stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportarInventario(Request $request)
    {
        $vendedor = $request->user()->vendedor;
        $productos = $vendedor->productos()
            ->select('codigo', 'nombre', 'marca', 'modelo', 'stock', 'stock_minimo', 'precio')
            ->get();

        return response()->json([
            'success' => true,
            'inventario' => $productos,
            'total' => $productos->count(),
            'fecha' => now()->format('Y-m-d H:i:s'),
            'vendedor' => $vendedor->nombre_comercial
        ]);
    }

    public function alertasStock(Request $request)
    {
        $vendedor = $request->user()->vendedor;

        $sinStock = $vendedor->productos()->where('stock', 0)->get([
            'id', 'nombre', 'codigo', 'stock', 'ventas'
        ]);

        $bajoStock = $vendedor->productos()->whereRaw('stock <= stock_minimo')
            ->where('stock', '>', 0)
            ->get([
                'id', 'nombre', 'codigo', 'stock', 'stock_minimo', 'ventas'
            ]);

        return response()->json([
            'success' => true,
            'sin_stock' => $sinStock,
            'bajo_stock' => $bajoStock,
            'total_sin_stock' => $sinStock->count(),
            'total_bajo_stock' => $bajoStock->count(),
        ]);
    }
}