<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VendedorAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendedor::with('user')->orderBy('created_at', 'desc');
        if ($request->filled('estado')) {
            if ($request->estado === 'verificados') {
                $query->where('verificado', true)->where('activo', true);
            } elseif ($request->estado === 'pendientes') {
                $query->where('verificado', false);
            } elseif ($request->estado === 'suspendidos') {
                $query->where('activo', false);
            }
        }
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            })->orWhere('nombre_comercial', 'LIKE', "%{$request->search}%")
              ->orWhere('rif', 'LIKE', "%{$request->search}%");
        }

        $vendedores = $query->paginate(20);

        return response()->json([
            'success' => true,
            'vendedores' => $vendedores,
            'estadisticas' => [
                'total' => Vendedor::count(),
                'verificados' => Vendedor::where('verificado', true)->count(),
                'pendientes' => Vendedor::where('verificado', false)->count(),
                'activos' => Vendedor::where('activo', true)->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $vendedor = Vendedor::with([
            'user',
            'productos',
            'pedidos',
            'reclamos'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'vendedor' => $vendedor
        ]);
    }

    public function verificar(Request $request, $id)
    {
        $vendedor = Vendedor::findOrFail($id);
        DB::beginTransaction();
        try {
            $vendedor->verificado = true;
            $vendedor->activo = true;
            $vendedor->membresia = $request->membresia ?? $vendedor->membresia;
            $vendedor->fecha_vencimiento_membresia = $request->fecha_vencimiento
                ? \Carbon\Carbon::parse($request->fecha_vencimiento)
                : now()->addMonth();
            $vendedor->save();

            $vendedor->user->tipo_usuario = 'vendedor';
            $vendedor->user->save();

            $vendedor->user->generarNotificacion(
                'Vendedor verificado',
                '¡Tu cuenta ha sido verificada! Ya puedes publicar productos.',
                'vendedor'
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Vendedor verificado exitosamente',
                'vendedor' => $vendedor->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
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

        $vendedor = Vendedor::findOrFail($id);
        DB::beginTransaction();
        try {
            $vendedor->verificado = false;
            $vendedor->activo = false;
            $vendedor->razon_rechazo = $request->razon;
            $vendedor->save();

            $vendedor->user->generarNotificacion(
                'Solicitud rechazada',
                "Tu solicitud como vendedor fue rechazada. Razón: {$request->razon}",
                'vendedor'
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada',
                'vendedor' => $vendedor
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function suspender(Request $request, $id)
    {
        $vendedor = Vendedor::findOrFail($id);
        $vendedor->activo = false;
        $vendedor->save();

        $vendedor->user->tipo_usuario = 'comprador';
        $vendedor->user->save();

        $vendedor->user->generarNotificacion(
            'Cuenta suspendida',
            'Tu cuenta de vendedor ha sido suspendida. Contacta soporte para más información.',
            'vendedor'
        );

        return response()->json([
            'success' => true,
            'message' => 'Vendedor suspendido',
            'vendedor' => $vendedor
        ]);
    }

    public function activar($id)
    {
        $vendedor = Vendedor::findOrFail($id);
        $vendedor->activo = true;
        $vendedor->save();

        $vendedor->user->tipo_usuario = 'vendedor';
        $vendedor->user->save();

        return response()->json([
            'success' => true,
            'message' => 'Vendedor activado',
            'vendedor' => $vendedor
        ]);
    }

    public function comisiones($id)
    {
        $comisiones = \App\Models\Comision::where('vendedor_id', $id)
            ->with('pedido')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'comisiones' => $comisiones
        ]);
    }
}