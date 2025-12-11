<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reclamo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReclamoAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Reclamo::with(['user', 'pedido.vendedor', 'asignadoA']);
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }
        if ($request->filled('tipo_reclamo')) {
            $query->where('tipo_reclamo', $request->tipo_reclamo);
        }
        if ($request->filled('search')) {
            $query->where('codigo_reclamo', 'LIKE', "%{$request->search}%");
        }

        $reclamos = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json([
            'success' => true,
            'reclamos' => $reclamos
        ]);
    }

    public function show($id)
    {
        $reclamo = Reclamo::with([
            'user',
            'pedido.vendedor',
            'seguimientos.user',
            'productoReemplazo'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'reclamo' => $reclamo
        ]);
    }

    public function asignar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reclamo = Reclamo::findOrFail($id);
        $reclamo->asignado_a = $request->usuario_id;
        $reclamo->save();

        $reclamo->generarNotificacionAsignacion();

        return response()->json([
            'success' => true,
            'message' => 'Reclamo asignado',
            'reclamo' => $reclamo
        ]);
    }

    public function resolver(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'solucion' => 'required|string|min:20',
            'reembolso_solicitado' => 'boolean',
            'monto_reembolso' => 'nullable|numeric|min:0',
            'producto_reemplazo_id' => 'nullable|exists:productos,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reclamo = Reclamo::findOrFail($id);
        $reclamo->procesarResolucion(
            $request->solucion,
            auth()->id()
        );

        if ($request->reembolso_solicitado) {
            $reclamo->reembolso_solicitado = true;
            $reclamo->monto_reembolso = $request->monto_reembolso ?? 0;
        }

        if ($request->producto_reemplazo_id) {
            $reclamo->producto_reemplazo_id = $request->producto_reemplazo_id;
        }

        $reclamo->save();

        return response()->json([
            'success' => true,
            'message' => 'Resolución aplicada',
            'reclamo' => $reclamo->fresh()
        ]);
    }

    public function cerrar($id)
    {
        $reclamo = Reclamo::findOrFail($id);
        $reclamo->estado = 'cerrado';
        $reclamo->save();

        return response()->json([
            'success' => true,
            'message' => 'Reclamo cerrado',
            'reclamo' => $reclamo
        ]);
    }

    public function estadisticas()
    {
        $estadisticas = [
            'total' => Reclamo::count(),
            'abiertos' => Reclamo::where('estado', 'abierto')->count(),
            'en_revision' => Reclamo::where('estado', 'en_revision')->count(),
            'resueltos' => Reclamo::where('estado', 'resuelto')->count(),
            'cerrados' => Reclamo::where('estado', 'cerrado')->count(),
            'por_tipo' => Reclamo::selectRaw('tipo_reclamo, COUNT(*) as total')->groupBy('tipo_reclamo')->get(),
            'por_prioridad' => Reclamo::selectRaw('prioridad, COUNT(*) as total')->groupBy('prioridad')->get(),
            'tiempo_promedio_respuesta' => Reclamo::whereNotNull('tiempo_respuesta')->avg('tiempo_respuesta'),
            'tasa_resolucion' => Reclamo::whereIn('estado', ['resuelto', 'cerrado'])->count() / max(Reclamo::count(), 1) * 100
        ];

        return response()->json([
            'success' => true,
            'estadisticas' => $estadisticas
        ]);
    }
}